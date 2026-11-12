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
use App\Models\StudentLog;
use App\Models\PaperTimetable;
use Auth;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;

class IaController extends Controller
{
    public function index($paperId){
        $paper = Paper::where("id",$paperId)->get()->first();
        // dd($paper->toArray());
        return view("pages.teacher.ia-attendance",compact('paper'));
    }

    public function pendingList() {
        $teacherId = auth('teacher')->id();

        $assignments = \App\Models\TimetableHeldPool::with([
                'course',
                'semester',
                'paperMaster'
            ])
            ->where('teacher_id', $teacherId)
            ->selectRaw('MIN(id) as id, course_id, semester_id, paper_master_id, section')
            ->groupBy('course_id', 'semester_id', 'paper_master_id', 'section')
            ->get();

        $coordinatorAssignments = PaperTimetable::query()
            ->where('teacher_id', $teacherId)
            ->where('is_coordinator', true)
            ->get(['course_id', 'semester', 'paper_id'])
            ->mapWithKeys(fn ($assignment) => [
                implode(':', [
                    $assignment->course_id,
                    $assignment->semester,
                    $assignment->paper_id,
                ]) => true,
            ]);

        $assignments->each(function ($assignment) use ($coordinatorAssignments) {
            $key = implode(':', [
                $assignment->course_id,
                $assignment->semester_id,
                $assignment->paper_master_id,
            ]);

            $assignment->setAttribute('is_coordinator', $coordinatorAssignments->has($key));
        });

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
            // ->where('section', $request->section)
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
        $assignment = $request->validate([
            'course_id' => 'required|integer',
            'semester_id' => 'required|integer',
            'paper_master_id' => 'required|integer',
        ]);

        abort_unless(
            $this->isCoordinatorForAssignment(
                (int) Auth::guard('teacher')->id(),
                (int) $assignment['course_id'],
                (int) $assignment['semester_id'],
                (int) $assignment['paper_master_id']
            ),
            403
        );

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
                'tute_attendance'=>$request->tuteAttendanceMarks[$studentId] ?? null,
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
        $assignment = \App\Models\TimetableHeldPool::findOrFail($assignmentId);

        abort_unless(
            $this->isCoordinatorForAssignment(
                (int) Auth::guard('teacher')->id(),
                (int) $assignment->course_id,
                (int) $assignment->semester_id,
                (int) $assignment->paper_master_id
            ),
            403
        );

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
        ->leftJoin('student_academic', 'student_academic.student_user_id', '=', 'student_users.id')
        ->orderBy('student_academic.roll_number')
        ->select('student_users.*')
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
                $log = StudentLog::where("student_user_id",$first['student_id'])->where('paper_master_id',$first['paper_master_id'])->first();
                $logCount = $log ? (int) $log->log_count : 0;
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
                        ? min(
                            round((($lecturePresent + $logCount) / $lectureWorking) * 100, 2),
                            100
                        )
                        : 0,

                    'tute_percentage' => $tuteWorking > 0
						? min(
							round(($tutePresent / $tuteWorking) * 100, 2),
							100
						)
						: 0,
                    'practical_percentage' => $practicalWorking > 0
						? min(
							round(($practicalPresent / $practicalWorking) * 100, 2),
							100
						)
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

    private function isCoordinatorForAssignment(
        int $teacherId,
        int $courseId,
        int $semesterId,
        int $paperMasterId
    ): bool {
        return PaperTimetable::query()
            ->where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('semester', $semesterId)
            ->where('paper_id', $paperMasterId)
            ->where('is_coordinator', true)
            ->exists();
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
        ->join('student_users', 'student_users.id', '=', 'ia_marks.student_id')
        ->where('ia_marks.paper_master_id', $paperId)
        ->where('ia_marks.semester_id', $semesterId)
        ->where('ia_marks.section', $section)
        ->where('ia_marks.created_by', $teacherId)
        ->leftJoin('student_academic', 'student_academic.student_user_id', '=', 'student_users.id')
        ->orderBy('student_academic.roll_number')
        ->select('ia_marks.*') 
        ->get();

        abort_if($marks->isEmpty(), 403, 'Unauthorized access');
        return view('pages.teacher.iaAttendance.history_show', compact('paper',
        'marks',
        'semesterId',
        'section'));

    }
 public function exportPdf($paperId, $semesterId, $section)
    {

        $teacherId = auth('teacher')->id();

        $paper = Paper::findOrFail($paperId);

        $marks = IaMark::with('student.academic')
            ->join('student_users', 'student_users.id', '=', 'ia_marks.student_id')
            ->where('ia_marks.paper_master_id', $paperId)
            ->where('ia_marks.semester_id', $semesterId)
            ->where('ia_marks.section', $section)
            ->where('ia_marks.created_by', $teacherId)
            ->leftJoin('student_academic', 'student_academic.student_user_id', '=', 'student_users.id')
            ->orderBy('student_academic.roll_number')
            ->select('ia_marks.*')
            ->get();

        abort_if($marks->isEmpty(), 403, 'Unauthorized access');

        $pdf = Pdf::loadView(
            'pages.teacher.iaAttendance.pdf',
            compact(
                'paper',
                'marks',
                'semesterId',
                'section'
            )
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'IA_Marks_' .
            $paper->code .
            '_Sem_' .
            $semesterId .
            '_Section_' .
            $section .
            '.pdf'
        );
    }

}
