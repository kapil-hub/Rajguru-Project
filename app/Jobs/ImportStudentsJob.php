<?php

namespace App\Jobs;

use App\Models\{
    Student, StudentAcademic, StudentEnrolDetail,
    StudentPaper, Departments, Courses, Paper
};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\{DB, Cache, Hash};

class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function handle()
    {
        $total = count($this->rows);
        $processed = 0;

        foreach ($this->rows as $row) {

            DB::transaction(function () use ($row) {

                $student = Student::create([
                    'control_number' => $row[0],
                    'name' => $row[1],
                    'email' => $row[2],
                    'mobile' => $row[3],
                    'admission_academic_year' => $row[4],
                    'password' => Hash::make('Student@123'),
                    'status' => 1,
                ]);

                $department = Departments::where('name', trim($row[6]))->first();
                $course = Courses::where('name', trim($row[7]))->first();

                StudentAcademic::create([
                    'student_user_id' => $student->id,
                    'roll_number' => $row[5],
                    'department_id' => $department->id,
                    'course_id' => $course->id,
                    'current_semester' => $row[8],
                    'section' => $row[9],
                    'current_academic_year' => $row[10],
                ]);

                StudentEnrolDetail::create([
                    'student_user_id' => $student->id,
                    'father_name' => $row[11],
                    'mother_name' => $row[12],
                    'parents_contact_number' => $row[13],
                    'parents_email_id' => $row[14],
                ]);

                $paperStart = 15;

                for ($i = 0; $i < 7; $i++) {
                    $code = trim($row[$paperStart + ($i * 3)]);
                    $type = trim($row[$paperStart + ($i * 3) + 1]);
                    $name = trim($row[$paperStart + ($i * 3) + 2]);
                    
                    if (!$code) continue;

                    $paper = Paper::where([
                        'code' => $code,
                        'course_id' => $course->id,
                        'semester' => $row[8],
                        'name' => $name,
                        'paper_type' => $type,
                    ])->first();
                    //Reattempt with 15 for SEC VAC GE 
                    if (!$paper && $type != 'DSC' && $type != 'DSE') {
                        $paper = Paper::where('code', $code)
                            ->where('semester', $row[8])
                            ->where('paper_type', $type)
                            ->where('name', $name)
                            ->where('course_id', 15)
                            ->first();
                    }


                    if ($paper) {
                        StudentPaper::create([
                            'student_user_id' => $student->id,
                            'paper_master_id' => $paper->id,
                            'semester' => $row[8],
                            'academic_year' => $row[10],
                        ]);
                    }
                }
            });

            // ✅ PROGRESS UPDATE (WORKING)
            $processed++;
            Cache::put(
                'student_import_progress',
                intval(($processed / $total) * 100)
            );
        }

        Cache::put('student_import_progress', 100);
    }
}
