<?php

namespace App\Imports;

use App\Models\Departments;
use App\Models\Courses;
use App\Models\Paper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PapersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $validRows = [];
    public array $invalidRows = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            /** 🔹 Skip completely empty rows (extra safety) */
            if (empty(array_filter($row->toArray()))) {
                continue;
            }

            /** 🔹 Required fields check */
            if (
                empty($row['department_name']) ||
                empty($row['course_name']) ||
                empty($row['paper_code'])
            ) {
                $this->invalidRows[] = [
                    'row'    => $index + 2,
                    'reason' => 'Required fields missing',
                    'data'   => $row,
                ];
                continue;
            }

            /** 🔹 Fetch Department & Course */
            $department = Departments::where('name', trim($row['department_name']))->first();
            $course     = Courses::where('name', trim($row['course_name']))->first();

            if (!$department || !$course) {
                $this->invalidRows[] = [
                    'row'    => $index + 2,
                    'reason' => 'Invalid department or course name',
                    'data'   => $row,
                ];
                continue;
            }

            /** 🔹 Duplicate paper check */
            $duplicate = Paper::where([
                'dept_id'   => $department->id,
                'course_id' => $course->id,
                'code'      => trim($row['paper_code']),
            ])->exists();

            if ($duplicate) {
                $this->invalidRows[] = [
                    'row'    => $index + 2,
                    'reason' => 'Duplicate paper code',
                    'data'   => $row,
                ];
                continue;
            }

            /** 🔹 Prepare valid row */
            $this->validRows[] = [
                'dept_id'               => $department->id,
                'course_id'             => $course->id,
                'semester'              => $row['semester'] ?? null,
                'code'                  => trim($row['paper_code']),
                'name'                  => trim($row['paper_name'] ?? ''),
                'paper_type'            => strtoupper(trim($row['paper_type_core_elective'] ?? '')),
                'status'                => ucfirst(strtolower(trim($row['status_active_inactive'] ?? ''))),
                'number_of_lectures'    => (int) ($row['number_of_lectures'] ?? 0),
                'number_of_tutorials'   => (int) ($row['number_of_tutorials'] ?? 0),
                'number_of_practicals'  => (int) ($row['number_of_practicals'] ?? 0),
            ];
        }
    }
}
