<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAttendance;
use App\Models\Paper;
use App\Models\Courses;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\AttendanceSetting;


class StudentAttendanceController extends Controller
{
     public function index()
{
    $studentId = auth('student')->id();

    $attendanceSettings = AttendanceSetting::where('status', 1)->latest()->first();

    if ($attendanceSettings->attendance_type === 'daily') {

        $attendance = \App\Models\StudentDailyAttendance::with('paper')
            ->where('student_id', $studentId)
            ->get()
            ->groupBy('paper_master_id')
            ->map(function ($records) {

                return collect([
                    (object)[
                        'paper' => $records->first()->paper,
                        'month' => now()->month,
                        'year'  => now()->year,

                        'lecture_working_days'   => $records->where('lecture', 1)->count(),
                        'lecture_present_days'   => $records->where('lecture', 1)->where('lecture_present', 1)->count(),

                        'tute_working_days'      => $records->where('tute', 1)->count(),
                        'tute_present_days'      => $records->where('tute', 1)->where('tute_present', 1)->count(),

                        'practical_working_days' => $records->where('practical', 1)->count(),
                        'practical_present_days' => $records->where('practical', 1)->where('practical_present', 1)->count(),
                    ]
                ]);
            });

    } else {

        $attendance = StudentAttendance::with('paper')
            ->where('student_id', $studentId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->groupBy('paper_master_id');
    }

    return view('pages.students.attendance.index', compact('attendance'));
}


    public function show($paperId, $month, $year)
    {
        $studentId = auth('student')->id();

        $records = StudentAttendance::where([
            'student_user_id' => $studentId,
            'paper_master_id' => $paperId,
            'month' => $month,
            'year' => $year,
        ])->get();

        return view('student.attendance.show', compact(
            'records', 'month', 'year'
        ));
    }
}
