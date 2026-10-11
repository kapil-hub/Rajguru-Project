<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportStudentDataJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentDataExportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $matchedStudents = $this->studentsQuery($filters)->distinct()->count('su.id');
        $sections = DB::table('student_academic')->whereNotNull('section')->where('section', '<>', '')
            ->distinct()->pluck('section')
            ->merge(DB::table('student_academic_history')->whereNotNull('section')->where('section', '<>', '')
                ->distinct()->pluck('section'))
            ->unique()->sort()->values();

        return view('pages.admin.students.export', [
            'departments' => DB::table('departments')->orderBy('name')->get(['id', 'name']),
            'courses' => DB::table('courses')->orderBy('name')->get(['id', 'name', 'dept_id']),
            'sections' => $sections,
            'matchedStudents' => $matchedStudents,
        ]);
    }

    public function start(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $total = $this->studentsQuery($filters)->distinct()->count('su.id');

        abort_if($total === 0, 422, 'No students match the selected filters.');

        $token = (string) Str::uuid();
        $key = $this->cacheKey($token);

        cache()->put($key, [
            'user_id' => auth('admin')->id(),
            'status' => 'queued',
            'progress' => 0,
            'processed' => 0,
            'total' => $total,
            'file' => "student_data_{$token}.xlsx",
        ], now()->addHour());

        ExportStudentDataJob::dispatch($filters, $token, $total);

        return response()->json([
            'token' => $token,
            'status_url' => route('admin.students.export.status', $token),
        ]);
    }

    public function status(string $token)
    {
        $export = $this->ownedExport($token);

        return response()->json([
            'status' => $export['status'],
            'progress' => $export['progress'],
            'processed' => $export['processed'],
            'total' => $export['total'],
            'download_url' => $export['status'] === 'completed'
                ? route('admin.students.export.download', $token)
                : null,
        ]);
    }

    public function download(string $token)
    {
        $export = $this->ownedExport($token);
        abort_unless($export['status'] === 'completed', 404);

        $path = 'exports/'.$export['file'];
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()
            ->download(Storage::disk('local')->path($path), 'student-data-export.xlsx')
            ->deleteFileAfterSend(true);
    }

    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'semester' => ['nullable', 'integer', 'between:1,8'],
            'section' => ['nullable', 'string', 'max:30'],
            'student_status' => ['nullable', 'in:all,active,exited'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $filters['student_status'] = $filters['student_status'] ?? 'all';

        return $filters;
    }

    private function studentsQuery(array $filters)
    {
        return DB::table('student_users as su')
            ->leftJoin('student_academic as sa', 'sa.student_user_id', '=', 'su.id')
            ->leftJoinSub(
                DB::table('student_academic_history')
                    ->selectRaw('MAX(id) as id, student_user_id')
                    ->groupBy('student_user_id'),
                'latest_history',
                fn ($join) => $join->on('latest_history.student_user_id', '=', 'su.id')
            )
            ->leftJoin('student_academic_history as sah', 'sah.id', '=', 'latest_history.id')
            ->leftJoin('departments as d', 'd.id', '=', 'sa.department_id')
            ->leftJoin('courses as c', 'c.id', '=', 'sa.course_id')
            ->when($filters['department_id'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.department_id', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.department_id', $value));
                });
            })
            ->when($filters['course_id'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.course_id', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.course_id', $value));
                });
            })
            ->when($filters['semester'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.current_semester', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.current_semester', $value));
                });
            })
            ->when($filters['section'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.section', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.section', $value));
                });
            })
            ->when(($filters['student_status'] ?? 'all') === 'active', fn ($query) => $query->whereNotNull('sa.id'))
            ->when(($filters['student_status'] ?? 'all') === 'exited', fn ($query) => $query->whereNull('sa.id'))
            ->when($filters['search'] ?? null, function ($query, $value) {
                $search = '%'.$value.'%';
                $query->where(function ($query) use ($search) {
                    $query->where('su.name', 'like', $search)
                        ->orWhere('su.control_number', 'like', $search)
                        ->orWhere('su.email', 'like', $search)
                        ->orWhere('sa.roll_number', 'like', $search)
                        ->orWhere('sa.college_roll_number', 'like', $search)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.roll_number', 'like', $search))
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.college_roll_number', 'like', $search));
                });
            });
    }

    private function ownedExport(string $token): array
    {
        abort_unless(Str::isUuid($token), 404);

        $export = cache()->get($this->cacheKey($token));
        abort_unless($export && $export['user_id'] === auth('admin')->id(), 404);

        return $export;
    }

    private function cacheKey(string $token): string
    {
        return 'student_data_export_'.$token;
    }
}