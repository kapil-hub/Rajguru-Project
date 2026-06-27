<?php

namespace App\Http\Controllers;

use App\Exports\StudentBlankTemplate;
use App\Imports\StudentsImport;
use App\Models\Courses;
use App\Models\Departments;
use App\Models\Paper;
use App\Models\Student;
use App\Models\StudentAcademic;
use App\Models\StudentAttendance;
use App\Models\StudentEnrolDetail;
use App\Models\StudentPaper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    use AuthorizesRequests;

    /* =======================
       STUDENT LIST
    ========================*/
    public function index(Request $request)
    {
        // 'current' = has a student_academic record (active students)
        // 'past'    = no student_academic record (alumni / removed)
        // 'all'     = everyone
        $view = $request->get('view', 'current');

        $query = Student::with(['academic.course', 'academic.department'])->latest();

        // ── View filter ──────────────────────────────────────────────
        if ($view === 'current') {
            $query->whereHas('academic');
        } elseif ($view === 'past') {
            $query->whereDoesntHave('academic');
        }
        // 'all' → no extra filter

        // ── Search ───────────────────────────────────────────────────
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('control_number', 'like', "%{$search}%");
            });
        }

        // ── Department filter ─────────────────────────────────────────
        if ($request->filled('department_id')) {
            $query->whereHas('academic', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // ── Course filter ─────────────────────────────────────────────
        if ($request->filled('course_id')) {
            $query->whereHas('academic', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        // ── Semester filter ───────────────────────────────────────────
        if ($request->filled('semester')) {
            $query->whereHas('academic', function ($q) use ($request) {
                $q->where('current_semester', $request->semester);
            });
        }

        $students    = $query->paginate(20)->withQueryString();
        $courses     = Courses::all();
        $departments = Departments::all();

        // Counts for the toggle tabs
        $totalCurrent = Student::whereHas('academic')->count();
        $totalPast    = Student::whereDoesntHave('academic')->count();
        $totalAll     = Student::count();

        return view('pages.students.index', compact(
            'students', 'courses', 'departments',
            'view', 'totalCurrent', 'totalPast', 'totalAll'
        ));
    }

    /* =======================
       CREATE PAGE
    ========================*/
    public function create()
    {

        return view('pages.students.create', [
            'departments' => Departments::all(),
            'courses' => Courses::all(),
            'allPapers' => Paper::select('id', 'name', 'code', 'semester')
                ->orderBy('name')->get(),
        ]);
    }

    /* =======================
       STORE STUDENT
    ========================*/
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'control_number' => 'required|unique:student_users,control_number',
            'email' => 'required|email|unique:student_users,email',
            'mobile' => 'required',
            'admission_academic_year' => 'required',

            'department_id' => 'required',
            'course_id' => 'required',
            'roll_number' => 'required',
            'current_semester' => 'required',
            'section' => 'required',

            'father_name' => 'required',
            'mother_name' => 'required',
            'parents_contact_number' => 'required',
            'parents_email_id' => 'required|email',
            'papers.*.paper_id' => 'required|distinct',
        ],
            [
                'papers.*.paper_id.distinct' => 'Same paper cannot be assigned twice.',
            ]);

        DB::transaction(function () use ($request) {

            $student = Student::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'admission_academic_year' => $request->admission_academic_year,
                'control_number' => $request->control_numer,
                'status' => 1,
                'password' => Hash::make('Student@123'),
            ]);

            StudentAcademic::create([
                'student_user_id' => $student->id,
                'roll_number' => $request->roll_number,
                'college_roll_number' => $request->college_roll_number,
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'current_semester' => $request->current_semester,
                'current_academic_year' => now()->year,
                'section' => $request->section,
            ]);

            StudentEnrolDetail::create([
                'student_user_id' => $student->id,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'parents_contact_number' => $request->parents_contact_number,
                'parents_email_id' => $request->parents_email_id,
            ]);

            foreach ($request->papers ?? [] as $paper) {
                $student->papers()->create([
                    'paper_master_id' => $paper['paper_id'],
                    'semester' => $paper['semester'] ?? $request->current_semester,
                    'academic_year' => now()->year,
                    'is_backlog' => $paper['is_backlog'] ?? 0,
                ]);
            }
        });

        return redirect()->route('students.index')
            ->with('success', 'Student added successfully');
    }

    /* =======================
       STUDENT PROFILE (SHOW)
    ========================*/
    public function show(Student $student)
    {

        $this->authorize('view', $student);
        $student->load([
            'academic.course',
            'academic.department',
            'enrolDetails',
            'papers.paper',
        ]);

        $academicHistory = [];
        if (auth('admin')->check()) {
            $historyRecords = DB::table('student_academic_history')
                ->leftJoin('departments', 'student_academic_history.department_id', '=', 'departments.id')
                ->leftJoin('courses', 'student_academic_history.course_id', '=', 'courses.id')
                ->where('student_academic_history.student_user_id', $student->id)
                ->select('student_academic_history.*', 'departments.name as department_name', 'courses.name as course_name')
                ->get();

            $historyPapers = DB::table('student_papers_history')
                ->join('paper_master', 'student_papers_history.paper_master_id', '=', 'paper_master.id')
                ->where('student_papers_history.student_user_id', $student->id)
                ->select('student_papers_history.*', 'paper_master.name as paper_name', 'paper_master.code as paper_code', 'paper_master.paper_type')
                ->get();

            $semesters = collect();

            foreach ($historyRecords as $record) {
                $sem = $record->current_semester;
                $semesters->put($sem, [
                    'is_current' => false,
                    'academic' => $record,
                    'papers' => $historyPapers->where('semester', $sem),
                ]);
            }

            if ($student->academic) {
                $currentSem = $student->academic->current_semester;
                if (!$semesters->has($currentSem)) {
                    $currentAcademicObj = (object)[
                        'roll_number' => $student->academic->roll_number,
                        'college_roll_number' => $student->academic->college_roll_number,
                        'department_name' => optional($student->academic->department)->name,
                        'course_name' => optional($student->academic->course)->name,
                        'current_semester' => $currentSem,
                        'section' => $student->academic->section,
                        'current_academic_year' => $student->academic->current_academic_year,
                    ];
                    
                    $currentPapersMapped = $student->papers->map(function($sp) {
                        return (object)[
                            'paper_code' => optional($sp->paper)->code,
                            'paper_name' => optional($sp->paper)->name,
                            'paper_type' => optional($sp->paper)->paper_type,
                            'is_backlog' => $sp->is_backlog,
                            'academic_year' => $sp->academic_year,
                        ];
                    });

                    $semesters->put($currentSem, [
                        'is_current' => true,
                        'academic' => $currentAcademicObj,
                        'papers' => $currentPapersMapped,
                    ]);
                }
            }

            $academicHistory = $semesters->sortByDesc(function ($value, $key) {
                return (int)$key;
            });
        }

        return view('pages.students.show', compact('student', 'academicHistory'));
    }

    /* =======================
       EDIT PAGE
    ========================*/
    public function edit(Student $student)
    {
        $this->authorize('update', $student);
        $allPapers = Paper::select('id', 'name', 'code', 'semester')->where('status','Active')->orderBy('name')->get();

        $student->load(['academic', 'enrolDetails', 'papers.paper']);

        $academicHistory = [];
        if (auth('admin')->check()) {
            $historyRecords = DB::table('student_academic_history')
                ->leftJoin('departments', 'student_academic_history.department_id', '=', 'departments.id')
                ->leftJoin('courses', 'student_academic_history.course_id', '=', 'courses.id')
                ->where('student_academic_history.student_user_id', $student->id)
                ->select('student_academic_history.*', 'departments.name as department_name', 'courses.name as course_name')
                ->get();

            $historyPapers = DB::table('student_papers_history')
                ->join('paper_master', 'student_papers_history.paper_master_id', '=', 'paper_master.id')
                ->where('student_papers_history.student_user_id', $student->id)
                ->select('student_papers_history.*', 'paper_master.name as paper_name', 'paper_master.code as paper_code', 'paper_master.paper_type')
                ->get();

            $semesters = collect();

            foreach ($historyRecords as $record) {
                $sem = $record->current_semester;
                $semesters->put($sem, [
                    'is_current' => false,
                    'academic' => $record,
                    'papers' => $historyPapers->where('semester', $sem),
                ]);
            }

            if ($student->academic) {
                $currentSem = $student->academic->current_semester;
                if (!$semesters->has($currentSem)) {
                    $currentAcademicObj = (object)[
                        'roll_number' => $student->academic->roll_number,
                        'college_roll_number' => $student->academic->college_roll_number,
                        'department_name' => optional($student->academic->department)->name,
                        'course_name' => optional($student->academic->course)->name,
                        'current_semester' => $currentSem,
                        'section' => $student->academic->section,
                        'current_academic_year' => $student->academic->current_academic_year,
                    ];
                    
                    $currentPapersMapped = $student->papers->map(function($sp) {
                        return (object)[
                            'paper_code' => optional($sp->paper)->code,
                            'paper_name' => optional($sp->paper)->name,
                            'paper_type' => optional($sp->paper)->paper_type,
                            'is_backlog' => $sp->is_backlog,
                            'academic_year' => $sp->academic_year,
                        ];
                    });

                    $semesters->put($currentSem, [
                        'is_current' => true,
                        'academic' => $currentAcademicObj,
                        'papers' => $currentPapersMapped,
                    ]);
                }
            }

            $academicHistory = $semesters->sortByDesc(function ($value, $key) {
                return (int)$key;
            });
        }

        return view('pages.students.edit', [
            'student' => $student,
            'courses' => Courses::all(),
            'departments' => Departments::all(),
            'allPapers' => $allPapers,
            'academicHistory' => $academicHistory,
        ]);
    }

    /* =======================
       UPDATE STUDENT
    ========================*/
    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $request->validate([

            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required|string|max:15',
            'admission_academic_year' => 'required|string',

            'roll_number' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'current_semester' => 'required|integer',
            'section' => 'required|string|max:5',

            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'parents_contact_number' => 'required|string|max:15',
            'parents_email_id' => 'required|email',
        ]);

        /* ===========================
           CHECK REMOVED PAPERS
        ============================*/

        $oldPaperIds = $student->papers()->pluck('paper_master_id')->toArray();

        $newPaperIds = collect($request->papers)
            ->pluck('paper_id')
            ->filter()
            ->toArray();

        $removedPaperIds = array_diff($oldPaperIds, $newPaperIds);

        /* ===========================
           CHECK ATTENDANCE EXISTS
        ============================*/

        $attendanceExists = false;

        if (! empty($removedPaperIds)) {

            $attendanceExists = StudentAttendance::where('student_id', $student->id)
                ->whereIn('paper_master_id', $removedPaperIds)
                ->where('semester_id', $request->current_semester)
                ->exists();
        }

        if ($request->ajax()) {

            if ($attendanceExists && ! $request->confirm_delete_attendance) {

                $papers = Paper::whereIn('id', $removedPaperIds)
                    ->get(['name', 'code'])
                    ->map(function ($p) {
                        return $p->name.' ('.$p->code.')';
                    });

                return response()->json([
                    'attendance_found' => true,
                    'papers' => $papers,
                ]);
            }

            return response()->json([
                'attendance_found' => false,
            ]);
        }

        DB::transaction(function () use ($request, $student, $removedPaperIds, $attendanceExists) {

            /* ================= STUDENT ================= */

            $student->update([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'admission_academic_year' => $request->admission_academic_year,
            ]);

            /* ================= ACADEMIC ================= */

            StudentAcademic::updateOrCreate(
                ['student_user_id' => $student->id],
                [
                    'roll_number' => $request->roll_number,
                    'college_roll_number' => $request->college_roll_number,
                    'department_id' => $request->department_id,
                    'course_id' => $request->course_id,
                    'current_semester' => $request->current_semester,
                    'section' => $request->section,
                    'current_academic_year' => now()->year,
                ]
            );

            /* ================= ENROLL ================= */

            StudentEnrolDetail::updateOrCreate(
                ['student_user_id' => $student->id],
                [
                    'father_name' => $request->father_name,
                    'mother_name' => $request->mother_name,
                    'parents_contact_number' => $request->parents_contact_number,
                    'parents_email_id' => $request->parents_email_id,
                ]
            );

            /* ================= DELETE ATTENDANCE ================= */

            if ($attendanceExists && $request->confirm_delete_attendance) {

                StudentAttendance::where('student_id', $student->id)
                    ->whereIn('paper_master_id', $removedPaperIds)
                    ->where('semester_id', $request->current_semester)
                    ->delete();
            }

            /* ================= UPDATE PAPERS ================= */

            $student->papers()->delete();

            foreach ($request->papers ?? [] as $paper) {

                $student->papers()->create([
                    'paper_master_id' => $paper['paper_id'],
                    'semester' => $paper['semester'] ?? $request->current_semester,
                    'academic_year' => now()->year,
                    'is_backlog' => $paper['is_backlog'] ?? 0,
                ]);
            }

        });

        return redirect()->back()->with('success', 'Student updated successfully');
    }

    /* =======================
       TEMPLATE DOWNLOAD
    ========================*/
    public function downloadTemplate()
    {
        return Excel::download(
            new StudentBlankTemplate,
            'students_import_template.xlsx'
        );
    }

    /* =======================
       IMPORT PREVIEW
    ========================*/
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        $import = new StudentsImport;
        Excel::import($import, $request->file('file'));

        return view('pages.students.confirm-import', [
            'validRows' => $import->validRows,
            'invalidRows' => $import->invalidRows,
        ]);
    }

    /* =======================
       IMPORT CONFIRM
    ========================*/
    public function confirmImport(Request $request)
    {
        DB::transaction(function () use ($request) {

            foreach ($request->valid_rows as $row) {

                $student = StudentUser::firstOrCreate(
                    ['control_number' => $row['college_roll_number']],
                    [
                        'name' => $row['student_name'],
                        'mobile' => $row['student_phone'],
                        'email' => $row['fathers_mothers_email_id'] ?? null,
                        'password' => Hash::make('student@123'),
                        'status' => 'Pending',
                    ]
                );

                $department = Departments::where('name', trim($row['dept']))->first();
                $course = Courses::where('name', trim($row['course']))->first();

                StudentAcademic::updateOrCreate(
                    ['student_user_id' => $student->id],
                    [
                        'roll_number' => $row['examination_rollnumber'] ?? null,
                        'department_id' => $department?->id,
                        'course_id' => $course?->id,
                        'current_semester' => $row['current_semester'],
                        'current_academic_year' => now()->year,
                    ]
                );

                for ($i = 1; $i <= 7; $i++) {
                    if (! empty($row["upc{$i}"])) {
                        StudentPaper::updateOrCreate(
                            [
                                'student_user_id' => $student->id,
                                'paper_code' => $row["upc{$i}"],
                            ],
                            [
                                'paper_type' => $row["papertype{$i}"] ?? null,
                                'paper_title' => $row["papertitle{$i}"] ?? null,
                                'semester' => $row['current_semester'],
                            ]
                        );
                    }
                }
            }
        });

        return redirect()->route('students.index')
            ->with('success', 'Students imported successfully');
    }
}
