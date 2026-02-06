<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StudentDailyAttendance;
use Illuminate\Http\Request;
use App\Models\Student;

class DailyAttendanceController extends Controller
{
    public function fillAttendance($assignmentId, $month, $year)
    {
        $assignment = \App\Models\TeacherClassAssignment::findOrFail($assignmentId);

        $students = Student::whereHas('academic', function ($q) use ($assignment) {
                $q->where('course_id', $assignment->course_id);
            })
            ->whereHas('papers', function ($q) use ($assignment) {
                $q->where('paper_master_id', $assignment->paper_master_id);
            })
            ->orderBy('name')
            ->get();

        return view(
            'pages.teacher.attendance.daily',
            compact('assignment', 'students', 'month', 'year')
        );
    }

    public function store(Request $request)
    {

        $students = Student::whereHas('academic', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            })
            ->whereHas('papers', function ($q) use ($request) {
                $q->where('paper_master_id', $request->paper_master_id);
            })
            ->orderBy('name')
            ->get();
        foreach ($students as $s) {
            $lecture   = $request->has("attendance.{$s->id}.lecture") ? 1 : 0;
            $tute      = $request->has("attendance.{$s->id}.tute") ? 1 : 0;
            $practical = $request->has("attendance.{$s->id}.practical") ? 1 : 0;

            StudentDailyAttendance::updateOrCreate(
                [
                    'student_id' => $s->id,
                    'attendance_date' => $request->attendance_date,
                    'paper_master_id' => $request->paper_master_id,
                ],
                [
                    'teacher_id' => auth('teacher')->id(),
                    'course_id' => $request->course_id,
                    'semester_id' => $request->semester_id,
                    'lecture' => $lecture,
                    'tute' => $tute,
                    'practical' => $practical,
                ]
            );
        }

        return back()->with('success', 'Daily attendance saved successfully.');
    }
}