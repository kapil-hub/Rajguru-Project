<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Departments;
use App\Models\Courses;
use App\Models\Paper;
use App\Models\FacultyDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminFacultyController extends Controller
{
   public function index()
    {
        $facultyUsers = Teacher::with('details')->orderBy('name')->get();
        return view('pages.admin.faculty.index', compact('facultyUsers'));
    }

    // Show create form
    public function create()
    {
        $departments = Departments::all();
        $courses     = Courses::all();
        $papers      = Paper::all();
        return view('pages.admin.faculty.create', compact('departments','courses','papers'));
    }

    // Store new faculty user
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'nullable|string|max:10',
            'name'         => 'required|string|max:255',
            'designation'  => 'required|string|max:100',
            'department_id'=> 'required|exists:departments,id',
            'mobile'       => 'nullable|string|max:20',
            'email'        => 'required|email|unique:faculty_users,email',
            'password'     => 'required|string|min:6|confirmed',
            'courses'      => 'required|array',
            'courses.*.course_id' => 'required|exists:courses,id',
            'courses.*.paper_master_id' => 'required|exists:paper_master,id',
        ]);

        $faculty = Teacher::create([
            'title'         => $request->title,
            'name'          => $request->name,
            'designation'   => $request->designation,
            'department_id' => $request->department_id,
            'mobile'        => $request->mobile,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'status'        => 1,
        ]);

        foreach($request->courses as $c) {
            FacultyDetail::create([
                'faculty_user_id' => $faculty->id,
                'department_id'   => $request->department_id,
                'course_id'       => $c['course_id'],
                'paper_master_id' => $c['paper_master_id'],
            ]);
        }

        return redirect()->route('admin.faculty.index')
                         ->with('success', 'Faculty created successfully');
    }

    // Show edit form
    public function edit($id)
    {
        $faculty = Teacher::with('details')->findOrFail($id);
        $departments = Departments::all();
        $courses = Courses::all();
        $papers = Paper::all();

        return view('pages.admin.faculty.edit', compact('faculty','departments','courses','papers'));
    }

    // Update faculty user
    public function update(Request $request, Teacher $faculty)
    {

        $request->validate([
            'title'        => 'nullable|string|max:10',
            'name'         => 'required|string|max:255',
            'designation'  => 'required|string|max:100',
            'department_id'=> 'required|exists:departments,id',
            'mobile'       => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                Rule::unique('faculty_users')->ignore($faculty->id),
            ],
            'password'     => 'nullable|string|min:6|confirmed',
            'details'      => 'required|array',
            'details.*.course_id' => 'required|exists:courses,id',
            'courses.*.paper_master_id' => 'required|exists:paper_master,id',
        ]);

        $faculty->update([
            'title'         => $request->title,
            'name'          => $request->name,
            'designation'   => $request->designation,
            'department_id' => $request->department_id,
            'mobile'        => $request->mobile,
            'email'         => $request->email,
        ]);

        if($request->filled('password')){
            $faculty->password = Hash::make($request->password);
            $faculty->save();
        }

        // Delete old details and insert new
        FacultyDetail::where('faculty_user_id', $faculty->id)->delete();
        foreach($request->details as $c) {
            FacultyDetail::create([
                'faculty_user_id' => $faculty->id,
                'department_id'   => $request->department_id,
                'course_id'       => $c['course_id'],
                'paper_master_id' => $c['paper_master_id'],
            ]);
        }

        return redirect()->route('admin.faculty.index')
                         ->with('success', 'Faculty updated successfully');
    }

    // Delete faculty
    public function destroy(Faculty $faculty)
    {
        FacultyDetail::where('faculty_user_id', $faculty->id)->delete();
        $faculty->delete();
        return redirect()->route('admin.faculty.index')
                         ->with('success', 'Faculty deleted successfully');
    }

}
