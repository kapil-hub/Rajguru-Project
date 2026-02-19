<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Exports\StudentBlankTemplate;
use App\Models\Student;
use App\Models\StudentAcademic;
use App\Models\StudentEnrolDetail;
use App\Models\StudentPaper;
use App\Models\Courses;
use App\Models\Paper;
use App\Models\Departments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StudentController extends Controller
{

    use AuthorizesRequests;
    /* =======================
       STUDENT LIST
    ========================*/
    public function index(Request $request)
    {
        $query = Student::with(['academic.course'])->latest();


        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('control_number', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20)->withQueryString();


        $courses = Courses::all();
        $departments = Departments::all();

        return view(
            'pages.students.index',
            compact('students', 'courses', 'departments')
        );
    }

    /* =======================
       CREATE PAGE
    ========================*/
    public function create()
    {
  
        return view('pages.students.create', [
            'departments' => Departments::all(),
            'courses'     => Courses::all(),
            'allPapers'   => Paper::select('id','name','code','semester')
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
        'papers.*.paper_id.distinct' => 'Same paper cannot be assigned twice.'
    ]);

        DB::transaction(function () use ($request) {

        $student = Student::create([
            'name'                     => $request->name,
            'email'                    => $request->email,
            'mobile'                   => $request->mobile,
            'admission_academic_year'  => $request->admission_academic_year,
            'control_number'           => $request->control_numer,
            'status'                   => 1,
            'password'                 => Hash::make('Student@123'),
        ]);

        StudentAcademic::create([
            'student_user_id' => $student->id,
            'roll_number'     => $request->roll_number,
            'college_roll_number'     => $request->college_roll_number,
            'department_id'   => $request->department_id,
            'course_id'       => $request->course_id,
            'current_semester'=> $request->current_semester,
            'current_academic_year' => now()->year,
            'section'         => $request->section,
        ]);

        StudentEnrolDetail::create([
            'student_user_id'        => $student->id,
            'father_name'            => $request->father_name,
            'mother_name'            => $request->mother_name,
            'parents_contact_number' => $request->parents_contact_number,
            'parents_email_id'       => $request->parents_email_id,
        ]);

        foreach ($request->papers ?? [] as $paper) {
            $student->papers()->create([
                'paper_master_id' => $paper['paper_id'],
                'semester' => $paper['semester'] ?? $request->current_semester,
                'academic_year'=>now()->year,
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
            'papers'
        ]);

        return view('pages.students.show', compact('student'));
    }

    /* =======================
       EDIT PAGE
    ========================*/
    public function edit(Student $student)
    {
        $this->authorize('update', $student);
        $allPapers = Paper::select('id', 'name', 'code', 'semester')->orderBy('name')->get();
        return view('pages.students.edit', [
            'student' => $student->load(['academic','enrolDetails',
                'papers.paper']),
            'courses' => Courses::all(),
            'departments' => Departments::all(),
            'allPapers' => $allPapers,
        ]);
    }

    /* =======================
       UPDATE STUDENT
    ========================*/
    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);
        $request->validate([
            // Student
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required|string|max:15',
            'admission_academic_year' => 'required|string',

            // Academic
            'roll_number' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'current_semester' => 'required|integer',
            'section' => 'required|string|max:5',

            // Parents
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'parents_contact_number' => 'required|string|max:15',
            'parents_email_id' => 'required|email',
        ]);

        DB::transaction(function () use ($request, $student) {

            /* ================= STUDENT USER ================= */
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
                    'college_roll_number'     => $request->college_roll_number,
                    'department_id' => $request->department_id,
                    'course_id' => $request->course_id,
                    'current_semester' => $request->current_semester,
                    'section' => $request->section,
                    'current_academic_year' => now()->year,
                ]
            );

            /* ================= ENROLL DETAILS ================= */
            StudentEnrolDetail::updateOrCreate(
                ['student_user_id' => $student->id],
                [
                    'father_name' => $request->father_name,
                    'mother_name' => $request->mother_name,
                    'parents_contact_number' => $request->parents_contact_number,
                    'parents_email_id' => $request->parents_email_id,
                ]
            );

            $student->papers()->delete();

            foreach ($request->papers ?? [] as $paper) {
                $student->papers()->create([
                    'paper_master_id' => $paper['paper_id'],
                    'semester' => $paper['semester'] ?? $request->current_semester,
                    'academic_year'=>now()->year,
                    'is_backlog' => $paper['is_backlog'] ?? 0,
                    
                ]);
            }



        });

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student details updated successfully');
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

        $import = new StudentsImport();
        Excel::import($import, $request->file('file'));

        return view('pages.students.confirm-import', [
            'validRows' => $import->validRows,
            'invalidRows' => $import->invalidRows
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
                        'status' => 'Pending'
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
                    if (!empty($row["upc{$i}"])) {
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
