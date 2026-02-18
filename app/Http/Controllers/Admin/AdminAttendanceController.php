<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentAttendanceMasterExport;
use App\Jobs\StudentAttendanceExportJob;
use Illuminate\Support\Str;
use App\Jobs\GenerateAttendanceExcelJob;
use Illuminate\Support\Facades\Storage;

class AdminAttendanceController extends Controller
{
   public function index(Request $request)
{
    $month = (int) ($request->month ?? now()->month);
    $year  = (int) ($request->year ?? now()->year);

    // 1️⃣ STUDENT SUMMARY (PAGINATED & FAST)
    $students = DB::table('student_attendances as sa')
        ->join('student_users as s', 's.id', '=', 'sa.student_id')
        ->join('student_academic as sc', 'sc.student_user_id', '=', 'sa.student_id')
        ->join('departments as d', 'd.id', '=', 'sc.department_id')
        ->join('courses as c', 'c.id', '=', 'sc.course_id')
        ->where('sa.month', $month)
        ->where('sa.year', $year)
        ->groupBy('sa.student_id', 's.name', 'sc.roll_number','d.name','c.name','sc.current_semester')
        ->select(
            'sa.student_id',
            's.name as student_name',
            'sc.roll_number',
            'd.name as department_name',
            'c.name as course_name',
            'sc.current_semester',
            DB::raw('ROUND(AVG(sa.lecture_present_days / NULLIF(sa.lecture_working_days,0)) * 100,2) as lecture_avg'),
            DB::raw('ROUND(AVG(sa.tute_present_days / NULLIF(sa.tute_working_days,0)) * 100,2) as tutorial_avg'),
            DB::raw('ROUND(AVG(sa.practical_present_days / NULLIF(sa.practical_working_days,0)) * 100,2) as practical_avg')
        )
        ->orderBy('s.name')
        ->paginate(25);

    // 2️⃣ LOAD PAPERS ONLY FOR CURRENT PAGE
    $studentIds = $students->pluck('student_id');

    $papers = DB::table('student_attendances as sa')
        ->join('paper_master as pm', 'pm.id', '=', 'sa.paper_master_id')
        ->whereIn('sa.student_id', $studentIds)
        ->where('sa.month', $month)
        ->where('sa.year', $year)
        ->select(
            'sa.student_id',
            'pm.name as paper_name',
            'sa.lecture_working_days',
            'sa.lecture_present_days',
            'sa.tute_working_days',
            'sa.tute_present_days',
            'sa.practical_working_days',
            'sa.practical_present_days'
        )
        ->get()
        ->groupBy('student_id');

    return view(
        'pages.admin.attendance-reports.master',
        compact('students','papers','month','year')
    );
}







   public function generateExcel(Request $request)
{
    $month = $request->month;
    $year  = $request->year;

    GenerateAttendanceExcelJob::dispatch(
        $month,
        $year,
        auth()->id()
    );

    return response()->json([
        'status' => 'started'
    ]);
}


public function checkExcelStatus()
{
    $file = cache()->get("excel_ready_user_" . auth()->id());

    if ($file) {
        return response()->json([
            'ready' => true,
            'download_route' => route('admin.attendance.download', $file)
        ]);
    }

    return response()->json(['ready' => false]);
}


public function downloadExcel($file)
{
    $relativePath = 'exports/' . $file;

    // Check in public disk (storage/app/public)
    if (!Storage::disk('public')->exists($relativePath)) {
        abort(404, 'File not found.');
    }

    $absolutePath = Storage::disk('public')->path($relativePath);

    cache()->forget("excel_ready_user_" . auth()->id());

    return response()
        ->download($absolutePath)
        ->deleteFileAfterSend(true);
}

}
