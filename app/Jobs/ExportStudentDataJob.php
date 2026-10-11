<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ExportStudentDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        private array $filters,
        private string $token,
        private int $total
    ) {}

    public function handle(): void
    {
        $key = $this->cacheKey();
        $export = Cache::get($key);
        if (! $export) {
            return;
        }

        $export['status'] = 'generating';
        Cache::put($key, $export, now()->addHour());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Student Data');

        $headings = [
            'Student ID', 'Student Name', 'Control Number', 'Email', 'Mobile',
            'Admission Academic Year', 'Roll Number', 'College Roll Number',
            'Department', 'Course', 'Current Semester', 'Section',
            'Current Academic Year', 'Student Status',
        ];

        foreach ($headings as $index => $heading) {
            $sheet->setCellValueExplicitByColumnAndRow($index + 1, 1, $heading, DataType::TYPE_STRING);
        }

        $sheet->freezePane('A2');

        $widths = [14, 26, 20, 30, 18, 22, 18, 22, 24, 24, 18, 14, 22, 18];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimensionByColumn($index + 1)->setWidth($width);
        }

        $processed = 0;
        $rowNumber = 2;
        $paperColumnCount = 0;

        $this->studentsQuery()
            ->select([
                'su.id',
                'su.name as student_name',
                'su.control_number',
                'su.email',
                'su.mobile',
                'su.admission_academic_year',
                'su.status as student_status',
                'sa.id as academic_id',
                'sa.roll_number',
                'sa.college_roll_number',
                'd.name as department_name',
                'c.name as course_name',
                'sa.current_semester',
                'sa.section',
                'sa.current_academic_year',
                'sah.roll_number as archived_roll_number',
                'sah.college_roll_number as archived_college_roll_number',
                'historical_department.name as archived_department_name',
                'historical_course.name as archived_course_name',
                'sah.current_semester as archived_current_semester',
                'sah.section as archived_section',
                'sah.current_academic_year as archived_current_academic_year',
            ])
            ->orderBy('su.id')
            ->chunkById(200, function ($students) use ($sheet, &$processed, &$rowNumber, &$paperColumnCount, $key) {
                $studentIds = $students->pluck('id');
                $studentsById = $students->keyBy('id');
                $currentPapers = DB::table('student_subject_registration_form as registration')
                    ->join('paper_master as paper', 'paper.id', '=', 'registration.paper_master_id')
                    ->whereIn('registration.student_user_id', $studentIds)
                    ->orderBy('paper.code')
                    ->get([
                        'registration.student_user_id',
                        'registration.semester',
                        'registration.paper_master_id',
                        'paper.code',
                        'paper.name',
                        'paper.paper_type',
                    ])
                    ->map(function ($paper) {
                        $paper->is_archived = false;

                        return $paper;
                    });
                $archivedPapers = DB::table('student_papers_history as registration')
                    ->join('paper_master as paper', 'paper.id', '=', 'registration.paper_master_id')
                    ->whereIn('registration.student_user_id', $studentIds)
                    ->orderBy('paper.code')
                    ->get([
                        'registration.student_user_id',
                        'registration.semester',
                        'registration.paper_master_id',
                        'paper.code',
                        'paper.name',
                        'paper.paper_type',
                    ])
                    ->map(function ($paper) {
                        $paper->is_archived = true;

                        return $paper;
                    });
                $papersByStudent = $currentPapers
                    ->merge($archivedPapers)
                    ->filter(function ($paper) use ($studentsById) {
                        $student = $studentsById->get($paper->student_user_id);
                        if (! $student || ($paper->is_archived && $student->academic_id)) {
                            return false;
                        }

                        $semester = $student?->academic_id
                            ? $student->current_semester
                            : $student?->archived_current_semester;

                        return trim((string) $semester) === trim((string) $paper->semester);
                    })
                    ->unique(fn ($paper) => $paper->student_user_id.':'.$paper->paper_master_id)
                    ->groupBy('student_user_id');

                foreach ($students as $student) {
                    $hasCurrentAcademic = $student->academic_id !== null;
                    $papers = ($papersByStudent[$student->id] ?? collect())
                        ->values();

                    while ($paperColumnCount < $papers->count()) {
                        $paperColumnCount++;
                        $firstPaperColumn = 15 + (($paperColumnCount - 1) * 3);
                        $paperHeadings = [
                            "Paper {$paperColumnCount} Code",
                            "Paper {$paperColumnCount} Name",
                            "Paper {$paperColumnCount} Type",
                        ];

                        foreach ($paperHeadings as $offset => $heading) {
                            $column = $firstPaperColumn + $offset;
                            $sheet->setCellValueExplicitByColumnAndRow($column, 1, $heading, DataType::TYPE_STRING);
                            $sheet->getColumnDimensionByColumn($column)->setWidth([15, 36, 15][$offset]);
                        }
                    }

                    $statusLabel = $hasCurrentAcademic ? 'Current' : 'Old';

                    $values = [
                        $student->id,
                        $student->student_name,
                        $student->control_number,
                        $student->email,
                        $student->mobile,
                        $student->admission_academic_year,
                        $hasCurrentAcademic ? $student->roll_number : $student->archived_roll_number,
                        $hasCurrentAcademic ? $student->college_roll_number : $student->archived_college_roll_number,
                        $hasCurrentAcademic ? $student->department_name : $student->archived_department_name,
                        $hasCurrentAcademic ? $student->course_name : $student->archived_course_name,
                        $hasCurrentAcademic ? $student->current_semester : $student->archived_current_semester,
                        $hasCurrentAcademic ? $student->section : $student->archived_section,
                        $hasCurrentAcademic ? $student->current_academic_year : $student->archived_current_academic_year,
                        $statusLabel,
                    ];

                    foreach ($values as $index => $value) {
                        $sheet->setCellValueExplicitByColumnAndRow(
                            $index + 1,
                            $rowNumber,
                            (string) ($value ?? ''),
                            DataType::TYPE_STRING
                        );
                    }

                    foreach ($papers as $paperIndex => $paper) {
                        $firstPaperColumn = 15 + ($paperIndex * 3);
                        foreach ([$paper->code, $paper->name, $paper->paper_type] as $offset => $value) {
                            $sheet->setCellValueExplicitByColumnAndRow(
                                $firstPaperColumn + $offset,
                                $rowNumber,
                                (string) ($value ?? ''),
                                DataType::TYPE_STRING
                            );
                        }
                    }

                    $rowNumber++;
                    $processed++;
                    if ($processed % 10 === 0 || $processed === $this->total) {
                        $this->updateProgress($key, $processed, 'generating');
                    }
                }
            }, 'su.id', 'id');

        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $rowNumber - 1;
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '176B57']],
        ]);
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

        $path = 'exports/student_data_'.$this->token.'.xlsx';
        Storage::disk('local')->makeDirectory('exports');
        (new Xlsx($spreadsheet))->save(Storage::disk('local')->path($path));
        $spreadsheet->disconnectWorksheets();

        $this->updateProgress($key, $this->total, 'completed', basename($path));
    }

    public function failed(Throwable $exception): void
    {
        $key = $this->cacheKey();
        $export = Cache::get($key);
        if (! $export) {
            return;
        }

        $export['status'] = 'failed';
        Cache::put($key, $export, now()->addHour());
        Storage::disk('local')->delete('exports/student_data_'.$this->token.'.xlsx');
    }

    private function studentsQuery()
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
            ->leftJoin('departments as historical_department', 'historical_department.id', '=', 'sah.department_id')
            ->leftJoin('courses as historical_course', 'historical_course.id', '=', 'sah.course_id')
            ->when($this->filters['department_id'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.department_id', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.department_id', $value));
                });
            })
            ->when($this->filters['course_id'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.course_id', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.course_id', $value));
                });
            })
            ->when($this->filters['semester'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.current_semester', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.current_semester', $value));
                });
            })
            ->when($this->filters['section'] ?? null, function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('sa.section', $value)
                        ->orWhere(fn ($query) => $query->whereNull('sa.id')->where('sah.section', $value));
                });
            })
            ->when(($this->filters['student_status'] ?? 'all') === 'active', fn ($query) => $query->whereNotNull('sa.id'))
            ->when(($this->filters['student_status'] ?? 'all') === 'exited', fn ($query) => $query->whereNull('sa.id'))
            ->when($this->filters['search'] ?? null, function ($query, $value) {
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

    private function updateProgress(string $key, int $processed, string $status, ?string $file = null): void
    {
        $export = Cache::get($key);
        if (! $export) {
            return;
        }

        $export['processed'] = $processed;
        $export['progress'] = min(100, (int) floor(($processed / max($this->total, 1)) * 100));
        $export['status'] = $status;
        if ($file) {
            $export['file'] = $file;
        }

        Cache::put($key, $export, now()->addHour());
    }

    private function cacheKey(): string
    {
        return 'student_data_export_'.$this->token;
    }
}