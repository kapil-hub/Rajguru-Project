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
    public function index(Request $request)
    {
        $query = Teacher::with(['details.course', 'details.paperMaster', 'department'])->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $facultyUsers = $query->paginate(15)->withQueryString();
        $departments = Departments::orderBy('name')->get();

        return view('pages.admin.faculty.index', compact('facultyUsers', 'departments'));
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
    public function edit($id = null)
    {
        if(is_null($id)){
            $id = auth('teacher')->user()->id;
        }
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

        return redirect()->back()
                         ->with('success', 'Faculty updated successfully');
    }

    // Delete faculty
    public function destroy(Teacher $faculty)
    {
        Teacher::where('id', $faculty->id)->delete();
        return redirect()->route('admin.faculty.index')
                         ->with('success', 'Faculty deleted successfully');
    }

}
