<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Models\Departments;
use App\Models\Courses;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PapersImport;
use App\Jobs\ExportPapersJob;
use Illuminate\Support\Str;


class PaperController extends Controller
{

    private const PAPER_TYPES = ['SEC','DSC','GE','VAC','DSE','AEC'];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $papers = $this->filteredPapers($filters)
            ->with(['department', 'course'])
            ->withCount('students')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pages.papers.index', [
            'papers' => $papers,
            'filters' => $filters,
            'matchedPapers' => $this->filteredPapers($filters)->count(),
            'departments' => Departments::orderBy('name')->get(['id', 'name']),
            'courses' => Courses::orderBy('name')->get(['id', 'name', 'dept_id']),
            'paperTypes' => self::PAPER_TYPES,
            'statuses' => ['Active', 'Inactive'],
            'semesters' => range(1, 8),
        ]);
    }

    public function startExport(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $total = $this->filteredPapers($filters)->count();

        abort_if($total === 0, 422, 'No papers match the selected filters.');

        $token = (string) Str::uuid();
        $key = $this->exportCacheKey($token);

        cache()->put($key, [
            'user_id' => auth('admin')->id(),
            'status' => 'queued',
            'progress' => 0,
            'processed' => 0,
            'total' => $total,
            'file' => "papers_{$token}.xlsx",
        ], now()->addHour());

        ExportPapersJob::dispatch($filters, $token, $total);

        return response()->json([
            'status_url' => route('papers.export.status', $token),
        ]);
    }

    public function exportStatus(string $token)
    {
        $export = $this->ownedExport($token);

        return response()->json([
            'status' => $export['status'],
            'progress' => $export['progress'],
            'processed' => $export['processed'],
            'total' => $export['total'],
            'download_url' => $export['status'] === 'completed'
                ? route('papers.export.download', $token)
                : null,
        ]);
    }

    public function downloadExport(string $token)
    {
        $export = $this->ownedExport($token);
        abort_unless($export['status'] === 'completed', 404);

        $path = 'exports/'.$export['file'];
        abort_unless(\Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);

        return response()
            ->download(\Illuminate\Support\Facades\Storage::disk('local')->path($path), 'papers-export.xlsx')
            ->deleteFileAfterSend(true);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'semester' => ['nullable', 'integer', 'between:1,8'],
            'semester_filter' => ['nullable', 'in:odd,even'],
            'paper_type' => ['nullable', 'in:SEC,DSC,GE,VAC,DSE,AEC'],
            'status' => ['nullable', 'in:Active,Inactive'],
        ]);
    }

    private function filteredPapers(array $filters)
    {
        return Paper::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('department', fn ($department) => $department->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['department_id'] ?? null, fn ($query, $value) => $query->where('dept_id', $value))
            ->when($filters['course_id'] ?? null, fn ($query, $value) => $query->where('course_id', $value))
            ->when($filters['semester'] ?? null, fn ($query, $value) => $query->where('semester', $value))
            ->when(($filters['semester_filter'] ?? null) === 'even', fn ($query) => $query->whereRaw('MOD(CAST(semester AS UNSIGNED), 2) = 0'))
            ->when(($filters['semester_filter'] ?? null) === 'odd', fn ($query) => $query->whereRaw('MOD(CAST(semester AS UNSIGNED), 2) != 0'))
            ->when($filters['paper_type'] ?? null, fn ($query, $value) => $query->where('paper_type', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value));
    }

    private function ownedExport(string $token): array
    {
        abort_unless(Str::isUuid($token), 404);

        $export = cache()->get($this->exportCacheKey($token));
        abort_unless($export && $export['user_id'] === auth('admin')->id(), 404);

        return $export;
    }

    private function exportCacheKey(string $token): string
    {
        return 'papers_export_'.$token;
    }

    public function create()
    {
        $departments = Departments::all();
        $courses = Courses::all();
        $papers = Paper::get();
        $paperTypes = self::PAPER_TYPES;
        return view('pages.papers.create', compact('departments', 'courses','papers','paperTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dept_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'semester' => 'required',
            'code' => 'required',
            'name' => 'required',
            'paper_type' => 'required',
            'status' => 'required',
        ]);

        // Unique check
        $exists = Paper::where([
            'dept_id' => $request->dept_id,
            'course_id' => $request->course_id,
            'semester' => $request->semester,
            'code' => $request->code
        ])->exists();

        if ($exists) {
            return back()->withErrors(['duplicate' => 'Paper with this Dept/Course/Semester/Code already exists']);
        }

        Paper::create($request->all());
        return redirect()->route('papers.index')->with('success', 'Paper added successfully');
    }

    public function downloadTemplate()
    {
        // Create blank Excel template with headers
        $headers = [
            'Department Name',
            'Course Name',
            'Semester',
            'Code',
            'Paper Name',
            'Paper Type',
            'Status',
            'Credit Of Lectures',
            'Credit Of Tutorials',
            'Credit Of Practicals'
        ];

        return Excel::download(new \App\Exports\BlankPaperTemplate($headers), 'papers_template.xlsx');
    }



    public function edit($id,Request $request){
        $paper = Paper::where("id",$id)->get()->first();
        if (!$paper) {
            return redirect()->back()->with("error", "Paper Does Not Exists");
        }
        $departments = Departments::all();
        $courses = Courses::all();
        $paperTypes = self::PAPER_TYPES;

        return view("pages.papers.edit",compact('paper','departments','courses','paperTypes'));
    }




    public function import(Request $request)
        {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv',
            ]);

            $import = new PapersImport();

            Excel::import($import, $request->file('file'));

            session([
                'paper_valid_rows' => $import->validRows
            ]);

            return view('pages.papers.import_confirmation', [
                'validRows' => $import->validRows,
                'invalidRows' => $import->invalidRows,
            ]);
        }

        public function confirmImport()
        {

            $rows = session('paper_valid_rows', []);

            Paper::insert($rows);

            session()->forget('paper_valid_rows');

            return redirect()
                ->route('papers.index')
                ->with('success', 'Papers imported successfully');
        }


        public function update($id,Request $request){
           $paper = Paper::where("id",$id);

           if($paper->update($request->except(['_token', '_method']))){
                return redirect()
                    ->route('papers.index')
                    ->with('success', 'Papers updated successfully');
            }else{
                return redirect()->back()->with("error","Something went wrong");
            }
        }

    public function showBatches(\App\Models\Paper $paper)
    {
        $studentPapers = \App\Models\StudentPaper::with('student.academic')
            ->where('paper_master_id', $paper->id)
            ->get()
            ->sortBy(function ($sp) {
                return $sp->student?->name ?? '';
            });

        $counts = $studentPapers->groupBy('batch')->map->count();

        return view('pages.papers.batches', compact('paper', 'studentPapers', 'counts'));
    }

    public function saveBatches(Request $request, \App\Models\Paper $paper)
    {
        $request->validate([
            'batches' => 'nullable|array',
            'batches.*' => 'nullable|string|max:10',
        ]);

        if ($request->filled('batches')) {
            foreach ($request->batches as $id => $batchName) {
                $batchName = $batchName ? strtoupper(trim($batchName)) : null;

                \App\Models\StudentPaper::where('id', $id)
                    ->where('paper_master_id', $paper->id)
                    ->update(['batch' => $batchName]);
            }
        }

        return redirect()->route('papers.index')->with('success', 'Student batches for this paper saved successfully.');
    }
}

