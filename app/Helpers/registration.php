<?php

use App\Models\RegistrationWindow;
use Carbon\Carbon;

if (! function_exists('is_registration_open')) {

    function is_registration_open()
    {
        if (!auth('student')->check()) {
            return false;
        }

        $student = auth('student')->user();

        $deptId   = $student->academic->department_id;
        $courseId = $student->academic->course_id;
        $today    = Carbon::today();

        return RegistrationWindow::where('department_id', $deptId)
            ->where('course_id', $courseId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists();
    }
}
