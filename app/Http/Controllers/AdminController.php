<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Courses;
use App\Models\Semester;
use App\Models\Paper;
use App\Models\TeacherClassAssignment;
use DB;

class AdminController extends Controller
{
    
   public function teacherAssignments() {
    $teachers = Teacher::all();
    $courses = Courses::all();
    $semesters = Semester::get(); 
    $papers = Paper::all();
    $sections = ['A','B','C'];
    if(auth('admin')->check()){
        $assignments = TeacherClassAssignment::whereHas('teacher')
            ->whereHas('course')
            ->whereHas('semester')
            ->whereHas('paperMaster')
            ->with(['teacher', 'course', 'semester', 'paperMaster'])
            ->get();
    }
    if(auth('teacher')->check()){
        $assignments = TeacherClassAssignment::where('teacher_id',auth('teacher')->user()->id)
            ->whereHas('teacher')
            ->whereHas('course')
            ->whereHas('semester')
            ->whereHas('paperMaster')->with(['teacher','course','semester','paperMaster'])->get();
    }
    


    return view('pages.admin.teacher_assignments.index', compact('teachers','courses','semesters','papers','sections','assignments'));
}

public function storeTeacherAssignment(Request $request) {
    TeacherClassAssignment::create([
        'teacher_id' => auth('admin')->check() ? $request->teacher_id : (auth('teacher')->check() ? auth('teacher')->user()->id : ' ' ),
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



   public function attendanceMonitorig(Request $request)
{
    $month = (int) ($request->month ?? now()->month);
    $year  = (int) ($request->year  ?? now()->year);

    $month = min(max($month, 1), 12);

    $teacherId = $request->teacher_id;  
    $status    = $request->status; 
    
    $records = DB::table('teacher_class_assignments as tca')
        ->select(
            'tca.id',
            'tca.teacher_id',
            'tca.course_id',
            'tca.semester_id',
            'tca.section',
            'tca.paper_master_id',
            DB::raw("
                EXISTS (
                    SELECT 1
                    FROM student_attendances sa
                   
                      WHERE sa.course_id = tca.course_id
                      AND sa.semester_id = tca.semester_id
                      AND sa.section = tca.section
                      AND sa.paper_master_id = tca.paper_master_id
                      AND sa.month = $month
                      AND sa.year = $year
                ) as is_marked
            ")
        );

    // 🔹 FILTER BY TEACHER
    if ($teacherId) {
        $records->where('tca.teacher_id', $teacherId);
    }

    // 🔹 FILTER BY STATUS
    if ($status === 'marked') {
        $records->whereRaw("
            EXISTS (
                SELECT 1 FROM student_attendances sa
                
                 WHERE sa.course_id = tca.course_id
                  AND sa.semester_id = tca.semester_id
                  AND sa.section = tca.section
                  AND sa.paper_master_id = tca.paper_master_id
                  AND sa.month = $month
                  AND sa.year = $year
            )
        ");
    }

    if ($status === 'not_marked') {
        $records->whereRaw("
            NOT EXISTS (
                SELECT 1 FROM student_attendances sa
              
                 WHERE  sa.course_id = tca.course_id
                  AND sa.semester_id = tca.semester_id
                  AND sa.section = tca.section
                  AND sa.paper_master_id = tca.paper_master_id
                  AND sa.month = $month
                  AND sa.year = $year
            )
        ");
    }

    $records = $records
        ->orderBy('tca.teacher_id')
        ->paginate(10)
        ->withQueryString();

    // Teachers for dropdown
    $teachers = \App\Models\Teacher::orderBy('name')->get();

    // COUNTS (same as before)
    $totalClasses = DB::table('teacher_class_assignments')->count();

    $markedCount = DB::table('teacher_class_assignments as tca')
        ->whereExists(function ($q) use ($month, $year) {
            $q->select(DB::raw(1))
              ->from('student_attendances as sa')
         
              ->whereColumn('sa.course_id', 'tca.course_id')
              ->whereColumn('sa.semester_id', 'tca.semester_id')
              ->whereColumn('sa.section', 'tca.section')
              ->whereColumn('sa.paper_master_id', 'tca.paper_master_id')
              ->where('sa.month', $month)
              ->where('sa.year', $year);
        })
        ->count();

    $notMarkedCount = $totalClasses - $markedCount;

    return view('pages.admin.attendance-settings.monitoring', compact(
        'records',
        'teachers',
        'month',
        'year',
        'teacherId',
        'status',
        'totalClasses',
        'markedCount',
        'notMarkedCount'
    ));
}


}
