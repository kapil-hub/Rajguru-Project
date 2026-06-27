<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Models\Departments;
use App\Models\Courses;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PapersImport;


class PaperController extends Controller
{

    private const PAPER_TYPES = ['SEC','DSC','GE','VAC','DSE','AEC'];

    public function index(Request $request)
    {
        $search = $request->query('search');
        $semesterFilter = $request->query('semester_filter');

        $papers = Paper::with(['department', 'course'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhereHas('department', function ($sub) use ($search) {
                          $sub->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('course', function ($sub) use ($search) {
                          $sub->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($semesterFilter === 'even', function ($query) {
                $query->whereRaw('MOD(CAST(semester AS UNSIGNED), 2) = 0');
            })
            ->when($semesterFilter === 'odd', function ($query) {
                $query->whereRaw('MOD(CAST(semester AS UNSIGNED), 2) != 0');
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pages.papers.index', compact('papers', 'search', 'semesterFilter'));
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

