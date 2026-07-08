<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Courses;
use App\Models\Semester;
use App\Models\Paper;
use DB;

class AdminController extends Controller
{
    



   public function attendanceMonitorig(Request $request)
{
    $month = (int) ($request->month ?? now()->month);
    $year  = (int) ($request->year  ?? now()->year);

    $month = min(max($month, 1), 12);

    $teacherId = $request->teacher_id;  
    $status    = $request->status; 
    
    $sectionMatches = 'sa.section COLLATE utf8mb4_unicode_ci = thp.section COLLATE utf8mb4_unicode_ci';

    $records = DB::table('timetable_held_pools as thp')
        ->select(
            'thp.id',
            'thp.teacher_id',
            'thp.course_id',
            'thp.semester_id',
            'thp.section',
            'thp.paper_master_id'
        )
        ->selectRaw(
            "
                EXISTS (
                    SELECT 1
                    FROM student_attendances sa
                    WHERE sa.course_id = thp.course_id
                      AND sa.semester_id = thp.semester_id
                      AND {$sectionMatches}
                      AND sa.paper_master_id = thp.paper_master_id
                      AND sa.month = ?
                      AND sa.year = ?
                ) as is_marked
            ",
            [$month, $year]
        )
        ->where('thp.month', $month)
        ->where('thp.year', $year);

    // 🔹 FILTER BY TEACHER
    if ($teacherId) {
        $records->where('thp.teacher_id', $teacherId);
    }

    // 🔹 FILTER BY STATUS
    if ($status === 'marked') {
        $records->whereRaw("
            EXISTS (
                SELECT 1 FROM student_attendances sa
                 WHERE sa.course_id = thp.course_id
                   AND sa.semester_id = thp.semester_id
                   AND {$sectionMatches}
                   AND sa.paper_master_id = thp.paper_master_id
                   AND sa.month = ?
                   AND sa.year = ?
            )
        ", [$month, $year]);
    }

    if ($status === 'not_marked') {
        $records->whereRaw("
            NOT EXISTS (
                SELECT 1 FROM student_attendances sa
                 WHERE sa.course_id = thp.course_id
                   AND sa.semester_id = thp.semester_id
                   AND {$sectionMatches}
                   AND sa.paper_master_id = thp.paper_master_id
                   AND sa.month = ?
                   AND sa.year = ?
            )
        ", [$month, $year]);
    }

    $records = $records
        ->orderBy('thp.teacher_id')
        ->paginate(10)
        ->withQueryString();

    // Teachers for dropdown
    $teachers = \App\Models\Teacher::orderBy('name')->get();

    // COUNTS
    $totalClasses = DB::table('timetable_held_pools')
        ->where('month', $month)
        ->where('year', $year)
        ->count();

    $markedCount = DB::table('timetable_held_pools as thp')
        ->where('thp.month', $month)
        ->where('thp.year', $year)
        ->whereExists(function ($q) use ($month, $year) {
            $q->select(DB::raw(1))
              ->from('student_attendances as sa')
              ->whereColumn('sa.course_id', 'thp.course_id')
              ->whereColumn('sa.semester_id', 'thp.semester_id')
              ->whereRaw('sa.section COLLATE utf8mb4_unicode_ci = thp.section COLLATE utf8mb4_unicode_ci')
              ->whereColumn('sa.paper_master_id', 'thp.paper_master_id')
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
