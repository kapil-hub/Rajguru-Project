<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationWindow;
use App\Models\Departments;
use App\Models\Courses;
use Illuminate\Http\Request;

class RegistrationWindowController extends Controller
{
     public function index()
    {
        $windows = RegistrationWindow::with(['department','course'])
            ->orderByDesc('created_at')
            ->get();

        $departments = Departments::all();
        $courses = Courses::all();

        return view('pages.admin.registration-window.index',
            compact('windows','departments','courses')
        );
    }



    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'course_id'     => 'required',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
        ]);

        RegistrationWindow::create($request->all());

        return back()->with('success', 'Registration window created');
    }

    public function create()
    {
        $departments = Departments::all();
        $courses = Courses::all();

        return view(
            'pages.admin.registration-window.create',
            compact('departments', 'courses')
        );
    }

    public function edit(RegistrationWindow $window)
    {
        $departments = Departments::all();
        $courses = Courses::all();

        return view('pages.admin.registration-window.edit',
            compact('window','departments','courses')
        );
    }

    public function update(Request $request, RegistrationWindow $window)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $window->update($request->only('start_date','end_date'));

        return redirect()
            ->route('admin.registration-window.index')
            ->with('success','Registration window updated');
    }

    public function toggle(RegistrationWindow $window)
    {
        $window->update([
            'is_active' => !$window->is_active
        ]);

        return back()->with('success','Status updated');
    }
}
