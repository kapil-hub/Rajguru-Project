<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAttendance;
use App\Models\Paper;
use App\Models\Courses;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\AttendanceSetting;
use App\Models\StudentDailyAttendance;
use DB;
use App\Exports\AttendanceTemplateExport;
use App\Imports\StudentAttendanceImport;
use Maatwebsite\Excel\Facades\Excel;
class AttendanceController extends Controller
{
    public function index()
        {
            $teacher = auth('teacher')->user();

            $papers = Paper::all();
            $courses = Courses::all();     
            $semesters = ['I','II','III','IV','V','VI','VII','VIII']; 
            $sections = ['A', 'B', 'C'];             
            return view('pages.teacher.attendance.index', compact('papers','courses','semesters','sections'));
        }

    public function loadStudents(Request $request)
    {
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $students = Student::whereHas('enrollments', function($q) use ($request) {
            $q->where('course_id', $request->course_id)
            ->where('semester_id', $request->semester_id)
            // ->where('section', $request->section)
            ->where('paper_master_id', $request->paper_id);
        })->get();
        $attendance = StudentAttendance::where('paper_master_id', $request->paper_id)
            // ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_user_id');

        return view('pages.teacher.attendance.partials.student-list',
            compact('students', 'attendance', 'date')
        );
    }

    public function storeAttendance(Request $request)
    {
        foreach ($request->attendance as $studentId => $types) {

            $data = [
                'teacher_id'      => auth('teacher')->id(),
                'student_id'      => $studentId,
                'paper_master_id' => $request->paper_master_id,
                'course_id'       => $request->course_id,
                'semester_id'     => $request->semester_id,
                'section'         => $request->section,
                'month'           => $request->month,
                'year'            => $request->year,
            ];

            // Lecture
            if (isset($types['lecture'])) {
                $data['lecture_working_days'] = $types['lecture']['working'] ?? null;
                $data['lecture_present_days'] = $types['lecture']['present'] ?? null;
            }

            // Tute
            if (isset($types['tute'])) {
                $data['tute_working_days'] = $types['tute']['working'] ?? null;
                $data['tute_present_days'] = $types['tute']['present'] ?? null;
            }

            // Practical
            if (isset($types['practical'])) {
                $data['practical_working_days'] = $types['practical']['working'] ?? null;
                $data['practical_present_days'] = $types['practical']['present'] ?? null;
            }

            StudentAttendance::updateOrCreate(
                [
                    'student_id'      => $studentId,
                    'paper_master_id' => $request->paper_master_id,
                    'course_id'       => $request->course_id,
                    'semester_id'     => $request->semester_id,
                    'section'         => $request->section,
                    'month'           => $request->month,
                    'year'            => $request->year,
                ],
                $data
            );
        }

        return redirect()
            ->route('teacher.attendance.pending')
            ->with('success', 'Attendance saved successfully');
    }



    public function pendingList() {
        $teacherId = auth('teacher')->id();

        $assignments = \App\Models\TeacherClassAssignment::with([
                'course',
                'semester',
                'paperMaster'
            ])
            ->where('teacher_id', $teacherId)
            ->where('is_active', 1)
            ->get();

        // Group attendance settings by semester for easy access
        $attendanceSettings = AttendanceSetting::where('status', 1)
            ->get()
            ->keyBy(fn ($s) => $s->academic_session . '_' . $s->semester_type);
            // echo "<pre>"; print_r($attendanceSettings->toArray());die;
        return view('pages.teacher.attendance.pending', compact(
            'assignments',
            'attendanceSettings'
        ));
    }

    public function fillAttendance($assignmentId, $month, $year)
    {
        $assignment = \App\Models\TeacherClassAssignment::findOrFail($assignmentId);

        $students = Student::with('academic')->where(function ($q) use ($assignment) {

            // Case 1: DSC / DSE → course required
            $q->whereHas('papers', function ($p) use ($assignment) {
                    $p->where('paper_master_id', $assignment->paper_master_id)
                    ->whereHas('paper', function ($pm) {
                        $pm->whereIn('paper_type', ['DSC', 'DSE']);
                    });
                })
                ->whereHas('academic', function ($a) use ($assignment) {
                    $a->where('course_id', $assignment->course_id);
                });

            // Case 2: Other paper types → ignore course
            $q->orWhereHas('papers', function ($p) use ($assignment) {
                $p->where('paper_master_id', $assignment->paper_master_id)
                ->whereHas('paper', function ($pm) {
                    $pm->whereNotIn('paper_type', ['DSC', 'DSE']);
                });
            });

        })
        ->orderBy('name')
        ->get();

        
        $oldAttendences = StudentAttendance::where(
                [
                    'paper_master_id' => $assignment->paper_master_id,
                    'course_id'       => $assignment->course_id,
                    'semester_id'     => $assignment->semester_id,
                    'month'=>$month,
                    'year'=>$year
                ]
            )->get()->keyBy('student_id');


        return view(
            'pages.teacher.attendance.fill',
            compact('assignment', 'students', 'month', 'year','oldAttendences')
        );
    }

public function history()
    {
        $teacherId = auth('teacher')->id();
        $attendanceSettings = AttendanceSetting::where('status', 1)
                ->get()->last();
        if($attendanceSettings->attendance_type == 'daily'){
            $records = StudentDailyAttendance::with('paper.course')->where('teacher_id', $teacherId)
            ->select('paper_master_id',
                'course_id',
                'semester_id',
                'section',
                DB::raw('MONTH(attendance_date) as month'),
                DB::raw('YEAR(attendance_date) as year'),
                )
                ->groupBy(
                'paper_master_id',
                'course_id',
                'semester_id',
                'section',
                'month',
                'year'
                )
                ->latest('year')
                ->latest('month')
                ->get();
        }else{
            $records = StudentAttendance::with('paper.course')->where('teacher_id', $teacherId)
                ->select(
                    'paper_master_id',
                    'course_id',
                    'semester_id',
                    'section',
                    'month',
                    'year'
                )
                ->groupBy(
                    'paper_master_id',
                    'course_id',
                    'semester_id',
                    'section',
                    'month',
                    'year'
                )
                ->latest('year')
                ->latest('month')
                ->get();
        }
        // echo "<pre>";print_r($records->toArray());die;
        return view('pages.teacher.attendance.history', compact('records'));
    }

    public function show($paperId, $month, $year)
    {
        $teacherId = auth('teacher')->id();
         $attendanceSettings = AttendanceSetting::where('status', 1)
                ->get()->last();
        if($attendanceSettings->attendance_type == 'daily'){

            $students = \App\Models\Student::whereHas('dailyAttendances', function ($q) use ($teacherId, $paperId, $month, $year) {
                $q->where('teacher_id', $teacherId)
                ->where('paper_master_id', $paperId)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year);
            })->get();

            $records = [];

            foreach ($students as $student) {
                $attendances = $student->dailyAttendances()
                    ->where('teacher_id', $teacherId)
                    ->where('paper_master_id', $paperId)
                    ->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year)
                    ->get();

                $lecture_working_days   = $attendances->where('lecture', 1)->count();
                $lecture_present_days   = $attendances->where('lecture', 1)->count();
                $tute_working_days      = $attendances->where('tute', 1)->count();
                $tute_present_days      = $attendances->where('tute', 1)->count();
                $practical_working_days = $attendances->where('practical', 1)->count();
                $practical_present_days = $attendances->where('practical', 1)->count();

                $records[] = (object)[
                    'student' => $student,
                    'lecture_working_days' => $lecture_working_days,
                    'lecture_present_days' => $lecture_present_days,
                    'tute_working_days' => $tute_working_days,
                    'tute_present_days' => $tute_present_days,
                    'practical_working_days' => $practical_working_days,
                    'practical_present_days' => $practical_present_days,
                ];

            }
            return view('pages.teacher.attendance.history_show', compact('records', 'month', 'year'));

        }else{
            $records = StudentAttendance::where([
                'teacher_id' => $teacherId,
                'paper_master_id' => $paperId,
                'month' => $month,
                'year' => $year,
            ])->with('student')->get();

            return view('pages.teacher.attendance.history_show', compact(
                'records', 'month', 'year'
            ));
        }
    }


    public function downloadTemplate(Request $request)
    {
        $students = json_decode($request->student_obj, true);

        return Excel::download(
            new AttendanceTemplateExport(
                students: $students,
                lectureWD: isset($request->lecture_days) ? $request->lecture_days : "hidden",
                tuteWD: isset($request->tute_days) ? $request->tute_days : "hidden",
                practicalWD: isset($request->practical_days) ? $request->practical_days :"hidden"
            ),
            'attendance_template.xlsx'
        );
    }

public function import(Request $request)
    {
        $request->validate([
            'file'            => 'required|mimes:xlsx',
            'paper_master_id' => 'required',
            'course_id'       => 'required',
            'semester_id'     => 'required',
            'section'         => 'required',
            'month'           => 'required',
            'year'            => 'required',
        ]);

        Excel::import(
            new StudentAttendanceImport(
                meta: [
                    'teacher_id'      => auth()->id(),
                    'paper_master_id' => $request->paper_master_id,
                    'course_id'       => $request->course_id,
                    'semester_id'     => $request->semester_id,
                    'section'         => $request->section,
                    'month'           => $request->month,
                    'year'            => $request->year,
                ]
            ),
            $request->file('file')
        );

        return back()->with('success', 'Attendance imported successfully');
    }

}
