<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;

class AdminAttendanceSettingController extends Controller
{
    public function index()
    {
        $setting = AttendanceSetting::where('status', 1)->first();
        return view('pages.admin.attendance-settings.index', compact('setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'attendance_type' => 'required|in:monthly,daily',
            'academic_session' => 'required',
            'semester_type' => 'required|in:odd,even',
            'start_month' => 'required|integer|min:1|max:12',
            'end_month' => 'required|integer|min:1|max:12',
        ]);

        AttendanceSetting::where('status', 1)->update(['status' => 0]);

        AttendanceSetting::create([
            'attendance_type' => $request->attendance_type,
            'academic_session' => $request->academic_session,
            'semester_type' => $request->semester_type,
            'start_month' => $request->start_month,
            'end_month' => $request->end_month,
            'status' => 1
        ]);

        return back()->with('success', 'Attendance configuration saved successfully');
    }
}
