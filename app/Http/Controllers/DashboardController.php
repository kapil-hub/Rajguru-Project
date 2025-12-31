<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Paper;
use App\Models\StudentAttendance;
use App\Models\StudentPaper;
use App\Models\StudentDailyAttendance;
use App\Models\AttendanceSetting;
use App\Models\TeacherClassAssignment;

class DashboardController extends Controller
{
    public function index()
    {
       if (Auth::guard('student')->check()) {

            $studentId = auth('student')->id();

            // Active attendance setting
            $setting = AttendanceSetting::where('status', 1)->first();

            // Total subjects (current semester)
            $totalSubjects = StudentPaper::where('student_user_id', $studentId)->count();

            // Totals
            $lectureHeld = $lecturePresent = 0;
            $tuteHeld = $tutePresent = 0;
            $practicalHeld = $practicalPresent = 0;

            $totalHeld = $totalPresent = 0;

            /* =========================
            MONTHLY ATTENDANCE
            ==========================*/
            if ($setting && $setting->attendance_type === 'monthly') {

                $records = StudentAttendance::where('student_id', $studentId)->get();

                foreach ($records as $row) {

                    // Lecture
                    $lectureHeld     += $row->lecture_working_days ?? 0;
                    $lecturePresent  += $row->lecture_present_days ?? 0;

                    // Tute
                    $tuteHeld        += $row->tute_working_days ?? 0;
                    $tutePresent     += $row->tute_present_days ?? 0;

                    // Practical
                    $practicalHeld   += $row->practical_working_days ?? 0;
                    $practicalPresent+= $row->practical_present_days ?? 0;
                }

                $totalHeld =
                    $lectureHeld +
                    $tuteHeld +
                    $practicalHeld;

                $totalPresent =
                    $lecturePresent +
                    $tutePresent +
                    $practicalPresent;
            }

            /* =========================
            DAILY ATTENDANCE
            ==========================*/
            else {

                $records = StudentDailyAttendance::where('student_id', $studentId)->get();

                foreach ($records as $row) {

                    // Lecture
                    if ($row->lecture !== null) {
                        $lectureHeld++;
                        if ($row->lecture == 1) $lecturePresent++;
                    }

                    // Tute
                    if ($row->tute !== null) {
                        $tuteHeld++;
                        if ($row->tute == 1) $tutePresent++;
                    }

                    // Practical
                    if ($row->practical !== null) {
                        $practicalHeld++;
                        if ($row->practical == 1) $practicalPresent++;
                    }
                }

                $totalHeld =
                    $lectureHeld +
                    $tuteHeld +
                    $practicalHeld;

                $totalPresent =
                    $lecturePresent +
                    $tutePresent +
                    $practicalPresent;
            }

            // Attendance Percentage
            $attendancePercent = $totalHeld > 0
                ? round(($totalPresent / $totalHeld) * 100, 2)
                : 0;

            return view('pages.dashboards.student', compact(
                'totalSubjects',
                'lectureHeld',
                'lecturePresent',
                'tuteHeld',
                'tutePresent',
                'practicalHeld',
                'practicalPresent',
                'setting',
                'attendancePercent'
            ));
        }

        // admin / teacher unchanged
        if (Auth::guard('admin')->check()) {

            $students = Student::get()->count();
            $teachers = Teacher::get()->count();
            $papers = Paper::get()->count();

            return view('pages.dashboards.admin',compact('students','teachers','papers'));
        }

        if (Auth::guard('teacher')->check()) {

            $teacher = Teacher::with('details')->where('id',auth('teacher')->user()->id)->first();
            $teacherSubjects = $teacher->details->count();

            $assignedClasses = TeacherClassAssignment::where('teacher_id',auth('teacher')->user()->id)->get()->count();

            return view('pages.dashboards.teacher',compact('teacherSubjects','assignedClasses'));
        }

        return redirect()->route('login');
    }
}
