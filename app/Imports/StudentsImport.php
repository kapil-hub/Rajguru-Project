<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentAcademic;
use App\Models\StudentPaper;
use App\Models\Departments;
use App\Models\Courses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class StudentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $validRows = [];
    public array $invalidRows = [];




    private function normalizeString(string $string): string
        {
            $string = strtolower($string);
            $string = preg_replace('/[^a-z0-9]/', '', $string);
            return $string;
        }


    public function collection(Collection $rows)
    {

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {

                if (empty(array_filter($row->toArray()))) {
                    continue;
                }

                /** REQUIRED FIELDS */
                if (
                    empty($row['student_name']) ||
                    empty($row['college_roll_number']) ||
                    empty($row['student_phone'])
                ) {
                    $this->invalidRows[] = [
                        'row' => $index + 2,
                        'reason' => 'Required fields missing',
                        'data' => $row
                    ];
                    continue;
                }

                /** DEPARTMENT & COURSE */
                // echo $row['dept']."------".$row['course']."<br>";continue;
                // $department = Departments::where('name', trim($row['dept']))->first();
                // $course     = Courses::where('name', trim($row['course']))->first();
                $inputDept = $this->normalizeString(trim($row['dept']));

                $department = Departments::all()->map(function ($dept) use ($inputDept) {
                    $dbDept = $this->normalizeString($dept->name);

                    similar_text($inputDept, $dbDept, $percent);

                    $dept->match_percent = $percent;
                    return $dept;
                })
                ->where('match_percent', '>=', 80)
                ->sortByDesc('match_percent')
                ->first();

                $inputCourse = $this->normalizeString(trim($row['course']));

                $course = Courses::all()->map(function ($course) use ($inputCourse) {
                    $dbCourse = $this->normalizeString($course->name);

                    similar_text($inputCourse, $dbCourse, $percent);

                    $course->match_percent = $percent;
                    return $course;
                })
                ->where('match_percent', '>=', 80)
                ->sortByDesc('match_percent')
                ->first();


                if (!$department || !$course) {
                    $this->invalidRows[] = [
                        'row' => $index + 2,
                        'reason' => 'Invalid department or course',
                        'data' => $row
                    ];
                    continue;
                }

                /** STUDENT USER */
                $student = Student::firstOrCreate(
                    ['control_number' => trim($row['college_roll_number'])],
                    [
                        'name' => trim($row['student_name']),
                        'mobile' => trim($row['student_phone']),
                        'email' => $row['fathers_mothers_email_id'] ?? null,
                        'password' => Hash::make('student@123'),
                        'status' => 'Pending',
                    ]
                );

                /** ACADEMIC DATA */
                StudentAcademic::updateOrCreate(
                    ['student_user_id' => $student->id],
                    [
                        'roll_number' => $row['examination_rollnumber'] ?? null,
                        'department_id' => $department->id,
                        'course_id' => $course->id,
                        'current_semester' => $row['current_semester'],
                        'current_academic_year' => now()->year,
                    ]
                );

                /** STUDENT PAPERS (UPC1–UPC7) */
                for ($i = 1; $i <= 7; $i++) {

                    $upc = $row["upc{$i}"] ?? null;
                    if (!$upc) continue;

                    StudentPaper::updateOrCreate(
                        [
                            'student_user_id' => $student->id,
                            'paper_code' => trim($upc),
                        ],
                        [
                            'paper_type' => $row["papertype{$i}"] ?? null,
                            'paper_title' => $row["papertitle{$i}"] ?? null,
                            'semester' => $row['current_semester'],
                        ]
                    );
                }

                $this->validRows[] = $student->id;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

