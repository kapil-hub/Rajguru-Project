<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departments;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Departments::latest()->paginate(10);
        return view('pages.admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Departments::whereRaw(
                        "LOWER(name) LIKE ?",
                        ["%" . strtolower($value) . "%"]
                    )->exists();

                    if ($exists) {
                        $fail('Similar department already exists.');
                    }
                }
            ]
        ]);


        Departments::create($request->only('name'));

        return redirect()->back()->with('success', 'Department Created Successfully');
    }

    public function update(Request $request, Departments $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id
        ]);

        $department->update($request->only('name'));

        return redirect()->back()->with('success', 'Department Updated Successfully');
    }

    public function destroy(Departments $department)
    {
        $department->delete();
        return redirect()->back()->with('success', 'Department Deleted Successfully');
    }
}
