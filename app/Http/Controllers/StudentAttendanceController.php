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
        $student = auth('student')->user()?->load(['academic', 'papers']);

        if (!$student || !$student->academic) {
            $attendance = collect();

            return view('pages.students.attendance.index', compact('attendance'));
        }

        $studentId = $student->id;
        $currentSemester = $student->academic->current_semester;
        $currentPaperIds = $student->papers->pluck('paper_master_id')->filter()->values();

        $attendanceSettings = AttendanceSetting::where('status', 1)->latest()->first();

        if ($attendanceSettings && $attendanceSettings->attendance_type === 'daily') {

            $attendance = \App\Models\StudentDailyAttendance::with('paper')
                ->where('student_id', $studentId)
                ->where('semester_id', $currentSemester)
                ->whereIn('paper_master_id', $currentPaperIds)
                ->orderBy('attendance_date')
                ->get()
                ->groupBy('paper_master_id')
                ->map(function ($records) {
                    return $records
                        ->groupBy(fn ($record) => $record->attendance_date->format('Y-n'))
                        ->map(function ($monthlyRecords) {
                            $firstRecord = $monthlyRecords->first();

                            return (object) [
                                'paper' => $firstRecord->paper,
                                'month' => $firstRecord->attendance_date->month,
                                'year' => $firstRecord->attendance_date->year,
                                'lecture_working_days' => $monthlyRecords->filter(fn ($record) => $record->lecture !== null)->count(),
                                'lecture_present_days' => $monthlyRecords->where('lecture', true)->count(),
                                'tute_working_days' => $monthlyRecords->filter(fn ($record) => $record->tute !== null)->count(),
                                'tute_present_days' => $monthlyRecords->where('tute', true)->count(),
                                'practical_working_days' => $monthlyRecords->filter(fn ($record) => $record->practical !== null)->count(),
                                'practical_present_days' => $monthlyRecords->where('practical', true)->count(),
                            ];
                        })
                        ->values();
                });

        } else {

            $attendance = StudentAttendance::with('paper')
                ->where('student_id', $studentId)
                ->where('semester_id', $currentSemester)
                ->whereIn('paper_master_id', $currentPaperIds)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->groupBy('paper_master_id');
        }

        return view('pages.students.attendance.index', compact('attendance'));
    }


    public function show($paperId, $month, $year)
    {
        $student = auth('student')->user();
        $studentId = $student->id;
        $currentPaperIds = $student->papers()->pluck('paper_master_id');

        $records = StudentAttendance::where([
            'student_id' => $studentId,
            'paper_master_id' => $paperId,
            'month' => $month,
            'year' => $year,
        ])
            ->whereIn('paper_master_id', $currentPaperIds)
            ->get();

        return view('student.attendance.show', compact(
            'records', 'month', 'year'
        ));
    }
}
