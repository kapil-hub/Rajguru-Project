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
use App\Models\IaMark;
use Auth;
use DB;

class IaController extends Controller
{
    public function index($paperId){
        $paper = Paper::where("id",$paperId)->get()->first();

        return view("pages.teacher.ia-attendance",compact('paper'));
    }

    public function pendingList() {
        $teacherId = auth('teacher')->id();

        $assignments = \App\Models\TeacherClassAssignment::with([
                'course',
                'semester',
                'paperMaster'
            ])
            ->where('teacher_id', $teacherId)
            ->get();

        // Group attendance settings by semester for easy access
        $attendanceSettings = AttendanceSetting::where('status', 1)
            ->get()
            ->keyBy(fn ($s) => $s->academic_session . '_' . $s->semester_type);
            // echo "<pre>"; print_r($attendanceSettings->toArray());die;
        return view('pages.teacher.iaAttendance.pending', compact(
            'assignments',
            'attendanceSettings'
        ));
    }



public function loadStudents(Request $request)
    {
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $students = Student::whereHas('enrollments', function($q) use ($request) {
            $q->where('course_id', $request->course_id)
            ->where('semester_id', $request->semester_id)
            ->where('section', $request->section)
            ->where('paper_master_id', $request->paper_id);
        })->get();
        $attendance = StudentAttendance::where('paper_master_id', $request->paper_id)
            // ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_user_id');

        return view('pages.teacher.iaAttendance.partials.student-list',
            compact('students', 'attendance', 'date')
        );
    }

    public function storeAttendance(Request $request)
    {
        
        foreach ($request->totalIaMarks as $studentId => $total) {

        IaMark::updateOrCreate(
            [
                'student_id' => $studentId,
                'paper_master_id' => $request->paper_master_id,
            ],
            [
                'course_id' => $request->course_id,
                'semester_id' => $request->semester_id,
                'section' => $request->section,

                'tute_ca' => $request->tuteCaMarks[$studentId] ?? null,
                'class_test' => $request->iaclassTest[$studentId] ?? null,
                'assignment' => $request->iaAssignment[$studentId] ?? null,
                'attendance' => $request->iaAttendance[$studentId] ?? null,
                'total' => $total,
                'total_tute_marks'=> $request->tuteCaMarks[$studentId] ?? null,
                'grand_total'=> $request->grandTotal[$studentId] ?? null,
                'created_by' => auth('teacher')->id(),
            ]
        );
    }

    return back()->with('success', 'IA Marks saved successfully');
    }

    public function fillAttendance($assignmentId)
    {
        $assignment = \App\Models\TeacherClassAssignment::findOrFail($assignmentId);

        $students = Student::whereHas('academic', function ($q) use ($assignment) {
                $q->where('course_id', $assignment->course_id)
                ->where('section', $assignment->section);
            })
            ->whereHas('papers', function ($q) use ($assignment) {
                $q->where('paper_master_id', $assignment->paper_master_id);
            })
            ->orderBy('name')
            ->get();
        
        $oldAttendences = StudentAttendance::where(
                [
                    'paper_master_id' => $assignment->paper_master_id,
                    'course_id'       => $assignment->course_id,
                    'semester_id'     => $assignment->semester_id,
                ]
            )->get()->groupBy('student_id');
            
        $oldAttendences = $oldAttendences->map(function ($records) {

                $first = $records->first();

                $lectureWorking   = $records->sum('lecture_working_days');
                $lecturePresent   = $records->sum('lecture_present_days');

                $tuteWorking      = $records->sum('tute_working_days');
                $tutePresent      = $records->sum('tute_present_days');

                $practicalWorking = $records->sum('practical_working_days');
                $practicalPresent = $records->sum('practical_present_days');

                return [
                    'student_id' => $first['student_id'],
                    'teacher_id' => $first['teacher_id'],
                    'paper_master_id' => $first['paper_master_id'],
                    'course_id' => $first['course_id'],
                    'semester_id' => $first['semester_id'],
                    'section' => $first['section'],

                    // TOTALS
                    'total_lecture_working_days'   => $lectureWorking,
                    'total_lecture_present_days'   => $lecturePresent,
                    'total_tute_working_days'      => $tuteWorking,
                    'total_tute_present_days'      => $tutePresent,
                    'total_practical_working_days' => $practicalWorking,
                    'total_practical_present_days' => $practicalPresent,

                    // PERCENTAGES
                    'lecture_percentage' => $lectureWorking > 0
                        ? round(($lecturePresent / $lectureWorking) * 100, 2)
                        : 0,

                    'tute_percentage' => $tuteWorking > 0
                        ? round(($tutePresent / $tuteWorking) * 100, 2)
                        : 0,

                    'practical_percentage' => $practicalWorking > 0
                        ? round(($practicalPresent / $practicalWorking) * 100, 2)
                        : 0,
                ];
            })->toArray();

            $oldData =  IaMark::where(
                    [
                    'paper_master_id' => $assignment->paper_master_id,
                        'course_id'       => $assignment->course_id,
                        'semester_id'     => $assignment->semester_id,
                    ])->get()->keyBy("student_id")->toArray();
            // echo "<pre>";print_r($oldData);die;
            
            return view(
                'pages.teacher.iaAttendance.fill',
                compact('assignment', 'students', 'oldAttendences','oldData')
            );
    }

public function history()
    {
        $teacherId = Auth::guard('teacher')->id();

        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $papers = DB::table('ia_marks')
            ->join('paper_master', 'paper_master.id', '=', 'ia_marks.paper_master_id')
            ->join('courses', 'courses.id', '=', 'ia_marks.course_id')
            ->where('ia_marks.created_by', $teacherId) // IMPORTANT
            ->where('ia_marks.updated_at', '>=', $sixMonthsAgo)
            ->select(
                'paper_master.id as paper_id',
                'paper_master.name',
                'courses.name',
                'ia_marks.semester_id',
                'ia_marks.section',
                DB::raw('COUNT(DISTINCT ia_marks.student_id) as students_count'),
                DB::raw('MAX(ia_marks.updated_at) as last_updated')
            )
            ->groupBy(
                'paper_master.id',
                'paper_master.name',
                'courses.name',
                'ia_marks.semester_id',
                'ia_marks.section'
            )
            ->orderByDesc('last_updated')
            ->get();
        return view('pages.teacher.iaAttendance.history', compact('papers'));
    }

    public function show($paperId, $semesterId, $section)
    {

    $teacherId = auth('teacher')->id();
    $paper = Paper::findOrFail($paperId);
    $marks = IaMark::with('student')
        ->where('paper_master_id', $paperId)
        ->where('semester_id', $semesterId)
        ->where('section', $section)
        ->where('created_by', $teacherId)
        ->orderBy('student_id')
        ->get();

        abort_if($marks->isEmpty(), 403, 'Unauthorized access');
        return view('pages.teacher.iaAttendance.history_show', compact('paper',
        'marks',
        'semesterId',
        'section'));

    }


}
