<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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

    $rows = Excel::toCollection(null, $request->file('file'))[0];

    $validRows = [];
    $invalidRows = [];

    foreach ($rows->skip(1) as $index => $row) {

        $errors = [];

        // =============================
        // BASIC STUDENT VALIDATION
        // =============================
        // if (!filter_var($row[2], FILTER_VALIDATE_EMAIL)) {
        //     $errors[] = 'Invalid student email';
        // }

        if (Student::where('email', $row[2])->exists()) {
            $errors[] = 'Student email already exists';
        }

        $department = Departments::where('name', trim($row[6]))->first();
        if (!$department) {
            $errors[] = 'Department not found';
        }

        $course = Courses::where('name', trim($row[7]))
            ->where('dept_id', optional($department)->id)
            ->first();

        if (!$course) {
            $errors[] = 'Course not found';
        }

        // =============================
        // PAPER VALIDATION (7 PAPERS)
        // =============================
        $paperStart = 15;
        $paperCount = 7;
        $validPaperFound = false;

        for ($i = 0; $i < $paperCount; $i++) {

            $code = trim($row[$paperStart + ($i * 3)]);
            $type = trim($row[$paperStart + ($i * 3) + 1]);
            $name = trim($row[$paperStart + ($i * 3) + 2]);

            // Skip empty paper slots
            if (!$code && !$type && !$name) {
                continue;
            }

            $validPaperFound = true;

            $paper = Paper::where([
                'code' => $code,
                'course_id' => optional($course)->id,
                'semester' => $row[8],
                'paper_type' => $type,
            ])->first();

            if (!$paper) {
                $errors[] = "Paper " . ($i + 1) . " not matched";
            }
        }

        if (!$validPaperFound) {
            $errors[] = 'At least one paper is required';
        }

        // =============================
        // FINAL DECISION
        // =============================
        if ($errors) {
            $invalidRows[] = [
                'row_number' => $index + 2,
                'errors' => $errors,
                'data' => $row,
            ];
        } else {
            $validRows[] = $row;
        }
    }

    session([
        'student_import_valid' => $validRows,
        'student_import_invalid' => $invalidRows,
    ]);

    return view('pages.students.import.preview', compact('validRows', 'invalidRows'));
}


    /* ===============================
     * STEP 3: CONFIRM IMPORT
     * =============================== */
   public function confirm()
    {
        $rows = session('student_import_valid', []);

        Cache::put('student_import_progress', 0);

        ImportStudentsJob::dispatch($rows);

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
