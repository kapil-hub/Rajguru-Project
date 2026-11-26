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
            ->where('sa.year', $year)
            ->whereExists(function ($currentPapers) {
                $currentPapers->selectRaw('1')
                    ->from('student_papers as sp')
                    ->whereColumn('sp.student_user_id', 'sa.student_id')
                    ->whereColumn('sp.paper_master_id', 'sa.paper_master_id');
            });

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
            $query->where('sa.semester_id', $request->semester);
        }
        if ($request->below75) {
            $query->havingRaw('SUM(sa.lecture_present_days) * 100.0 / NULLIF(SUM(sa.lecture_working_days), 0) < 75');
        }

        if ($request->above90) {
            $query->havingRaw('SUM(sa.lecture_present_days) * 100.0 / NULLIF(SUM(sa.lecture_working_days), 0) >= 90');
        }
        $students = $query
            ->groupBy('sa.student_id', 's.name', 'sc.roll_number', 'd.name', 'c.name', 'sa.semester_id')
            ->select(
                'sa.student_id',
                's.name as student_name',
                'sc.roll_number',
                'd.name as department_name',
                'c.name as course_name',
                'sa.semester_id as current_semester',
                DB::raw('ROUND(SUM(sa.lecture_present_days) * 100.0 / NULLIF(SUM(sa.lecture_working_days), 0), 2) as lecture_avg'),
                DB::raw('ROUND(SUM(sa.tute_present_days) * 100.0 / NULLIF(SUM(sa.tute_working_days), 0), 2) as tutorial_avg'),
                DB::raw('ROUND(SUM(sa.practical_present_days) * 100.0 / NULLIF(SUM(sa.practical_working_days), 0), 2) as practical_avg')
            )
            ->orderBy('s.name')
            ->paginate(25)
            ->withQueryString();
        // 2️⃣ LOAD PAPERS ONLY FOR CURRENT PAGE
        $studentIds = $students->pluck('student_id');
        $departments = DB::table('departments')->get();
        $courses = DB::table('courses')->get();
        $currentYear = now()->year;
        $next = $currentYear + 1;
        $prev = $currentYear - 1;
        $session_1 = $currentYear.'-'.substr($next, -2);
        $session_2 = $prev.'-'.substr($currentYear, -2);
        $buttons = AttendanceSetting::whereIn('academic_session', [$session_1, $session_2])->orderBy('id')->get()->last();
        $markedMonths = StudentAttendance::where('year', $currentYear)
            ->whereExists(function ($currentPapers) {
                $currentPapers->selectRaw('1')
                    ->from('student_papers as sp')
                    ->whereColumn('sp.student_user_id', 'student_attendances.student_id')
                    ->whereColumn('sp.paper_master_id', 'student_attendances.paper_master_id');
            })
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
