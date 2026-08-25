<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\StudentTemplateExport;
use App\Jobs\ImportStudentsJob;
use App\Models\{
    Student,
    StudentAcademic,
    StudentEnrolDetail,
    StudentPaper,
    Departments,
    Courses,
    Paper
};
use Illuminate\Support\Facades\{
    DB, Hash, Cache
};

class StudentImportController extends Controller
{
    private function findMatchingPaper(string $code, string $type, string $name, ?int $courseId, $semester): ?Paper
    {
        $query = Paper::where('code', $code)
            ->where('course_id', $courseId)
            ->where('semester', $semester)
            ->where('paper_type', $type);

        $paper = $query->get()->first(
            fn (Paper $paper) => trim((string) $paper->name) === $name
        );

        if ($paper || in_array($type, ['DSC', 'DSE'])) {
            return $paper;
        }

        return Paper::where('code', $code)
            ->where('semester', $semester)
            ->where('paper_type', $type)
            ->where('course_id', 15)
            ->get()
            ->first(fn (Paper $paper) => trim((string) $paper->name) === $name);
    }

    /* ===============================
     * STEP 1: DOWNLOAD TEMPLATE
     * =============================== */
    public function template()
    {
        return Excel::download(
            new StudentTemplateExport,
            'student_import_template.xlsx'
        );
    }

    /* ===============================
     * STEP 2: PREVIEW & VALIDATE
     * =============================== */
   public function preview(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx'
    ]);

    /* ===============================
       STORE FILE IN PUBLIC DIRECTORY
       =============================== */
    $importDir = public_path('imports');
    if (!file_exists($importDir)) {
        mkdir($importDir, 0777, true);
    }

    $fileName = uniqid('students_') . '.xlsx';
    $request->file('file')->move($importDir, $fileName);

    // store filename in session (IMPORTANT)
    session(['student_import_file' => $fileName]);

    $filePath = public_path('imports/' . $fileName);

    /* ===============================
       READ EXCEL
       =============================== */
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    $validRows = [];
    $invalidRows = [];

    foreach ($sheet->getRowIterator(2) as $index => $row) {

        $cells = [];
        foreach ($row->getCellIterator() as $cell) {
            $cells[] = trim((string) $cell->getValue());
        }

        $errors = [];

        // =============================
        // BASIC VALIDATION
        // =============================
        if (Student::where('email', $cells[2] ?? null)->exists()) {
            $errors[] = 'Student email already exists';
        }

        $department = Departments::where('name', trim($cells[6] ?? ''))->first();
        if (!$department) {
            $errors[] = 'Department not found';
        }

        $course = Courses::where('name', trim($cells[7] ?? ''))
            ->where('dept_id', optional($department)->id)
            ->first();

        if (!$course) {
            $errors[] = 'Course not found';
        }

        // =============================
        // PAPER VALIDATION
        // =============================
        $paperStart = 15;
        $validPaperFound = false;

        for ($i = 0; $i < 7; $i++) {

            $code = trim($cells[$paperStart + ($i * 3)] ?? '');
            $type = trim($cells[$paperStart + ($i * 3) + 1] ?? '');
            $name = trim($cells[$paperStart + ($i * 3) + 2] ?? '');

            if (!$code && !$type && !$name) {
                continue;
            }

            $validPaperFound = true;

            $paper = $this->findMatchingPaper(
                $code,
                $type,
                $name,
                optional($course)->id,
                $cells[8] ?? null
            );

            if (!$paper) {
                $errors[] = "Paper " . ($i + 1) . " not matched";
            }
        }

        // =============================
        // FINAL RESULT
        // =============================
        if ($errors) {
            $invalidRows[] = [
                'row_number' => $index,
                'errors' => $errors,
                'data' => $cells,
            ];
        } else {
            $validRows[] = $cells;
        }
    }

    // store valid rows for confirmation
    session(['student_import_valid' => $validRows]);

    return view(
        'pages.students.import.preview',
        compact('validRows', 'invalidRows')
    );
}

    /* ===============================
     * STEP 3: CONFIRM IMPORT
     * =============================== */
   public function confirm()
{
    $rows = session('student_import_valid', []);

    if (empty($rows)) {
        return back()->with('error', 'No data to import');
    }

    $total = count($rows);

    Cache::put('student_import_total', $total);
    Cache::put('student_import_processed', 0);
    Cache::put('student_import_progress', 0);
    Cache::put('chunk_processed',0);
    Cache::put('student_import_file', session('student_import_file'));
    // ?? CHUNK DATA (100 rows per job)
    $chunks = array_chunk($rows, 100);
    Cache::put('total_jobs_chunk',count($chunks));
    foreach ($chunks as $chunk) {
        ImportStudentsJob::dispatch($chunk);
    }

    return redirect()->route('students.import.progress');
}




    /* ===============================
     * STEP 4: PROGRESS API
     * =============================== */
 public function progress()
{
    return  view('pages.students.import.progress');
}

public function progressStatus()
{
    return response()->json([
        'progress' => Cache::get('student_import_progress', 0)
    ]);
}
}
