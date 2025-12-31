<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Courses;
use App\Models\Semester;
use App\Models\Paper;
use App\Models\TeacherClassAssignment;

class AdminController extends Controller
{
    
   public function teacherAssignments() {
    $teachers = Teacher::all();
    $courses = Courses::all();
    $semesters = Semester::get(); 
    $papers = Paper::all();
    $sections = ['A','B','C'];

    $assignments = TeacherClassAssignment::with(['teacher','course','semester','paperMaster'])->get();

    return view('pages.admin.teacher_assignments.index', compact('teachers','courses','semesters','papers','sections','assignments'));
}

public function storeTeacherAssignment(Request $request) {
    TeacherClassAssignment::create([
        'teacher_id' => $request->teacher_id,
        'course_id' => $request->course_id,
        'semester_id' => $request->semester_id,
        'section' => $request->section,
        'paper_master_id' => $request->paper_master_id,
        'academic_session'=> $request->academic_session,

        'is_lecture' => $request->has('is_lecture'),
        'is_tute' => $request->has('is_tute'),
        'is_practical' => $request->has('is_practical'),
        'is_coordinator' => $request->has('is_coordinator'),
    ]);


    return back()->with('success','Teacher assigned successfully');
}


public function toggleStatus($id)
{
    $assignment = TeacherClassAssignment::findOrFail($id);
    $assignment->is_active = !$assignment->is_active;
    $assignment->save();

    return back()->with('success', 'Assignment status updated');
}

}
