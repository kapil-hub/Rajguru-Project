<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAttendanceExcelJob;
use App\Models\AttendanceSetting;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // Month & Year
        if ($request->filled('month_year')) {
            [$year, $month] = explode('-', $request->month_year);
        } else {
            $month = now()->month;
            $year = now()->year;
        }

        $query = DB::table('student_attendances as sa')
            ->join('student_users as s', 's.id', '=', 'sa.student_id')
            ->join('student_academic as sc', 'sc.student_user_id', '=', 'sa.student_id')
            ->join('departments as d', 'd.id', '=', 'sc.department_id')
            ->join('courses as c', 'c.id', '=', 'sc.course_id')
            ->where('sa.month', $month)
            ->where('sa.year', $year);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('s.name', 'like', '%'.$request->search.'%')
                    ->orWhere('sc.roll_number', 'like', '%'.$request->search.'%');
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('sc.department_id', $request->department_id);
        }

        // Course filter
        if ($request->filled('course_id')) {
            $query->where('sc.course_id', $request->course_id);
        }

        // Semester filter
        if ($request->filled('semester')) {
            $query->where('sc.current_semester', $request->semester);
        }
        if ($request->below75) {
            $query->havingRaw('AVG(sa.lecture_present_days / NULLIF(sa.lecture_working_days,0)) * 100 < 50');
        }

        if ($request->above90) {
            $query->havingRaw('AVG(sa.lecture_present_days / NULLIF(sa.lecture_working_days,0)) * 100 >= 66');
        }
        $students = $query
            ->groupBy('sa.student_id', 's.name', 'sc.roll_number', 'd.name', 'c.name', 'sc.current_semester')
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
            ->paginate(25)
            ->withQueryString();
        // 2️⃣ LOAD PAPERS ONLY FOR CURRENT PAGE
        $studentIds = $students->pluck('student_id');
        $departments = DB::table('departments')->get();
        $courses = DB::table('courses')->get();
        $year = date('Y');
        $next = $year + 1;
        $prev = $year - 1;
        $session_1 = $year.'-'.substr($next, -2);
        $session_2 = $prev.'-'.substr($year, -2);
        $buttons = AttendanceSetting::whereIn('academic_session', [$session_1, $session_2])->orderBy('id')->get()->last();
        $markedMonths = StudentAttendance::where('year', now()->year)
            ->distinct()
            ->select('month', 'year')->get()->keyBy('month')
            ->toArray();

        return view(
            'pages.admin.attendance-reports.master',
            compact('students', 'month', 'year', 'departments', 'courses', 'buttons', 'markedMonths')
        );

    }

    public function generateExcel(Request $request)
    {
        $month = $request->month;
        $year = $request->year;
        $breakup = $request->breakup;
        GenerateAttendanceExcelJob::dispatch(
            $month,
            $year,
            $breakup,
            auth()->id()
        );

        return response()->json([
            'status' => 'started',
        ]);
    }

    public function checkExcelStatus()
    {
        $file = cache()->get('excel_ready_user_'.auth()->id());

        if ($file) {
            return response()->json([
                'ready' => true,
                'download_route' => route('admin.attendance.download', $file),
            ]);
        }

        return response()->json(['ready' => false]);
    }

    public function downloadExcel($file)
    {
        $relativePath = 'exports/'.$file;

        // Check in public disk (storage/app/public)
        if (! Storage::disk('public')->exists($relativePath)) {
            abort(404, 'File not found.');
        }

        $absolutePath = Storage::disk('public')->path($relativePath);

        cache()->forget('excel_ready_user_'.auth()->id());

        return response()
            ->download($absolutePath)
            ->deleteFileAfterSend(true);
    }
}
