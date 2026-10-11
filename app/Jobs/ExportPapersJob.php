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

class ExportPapersJob implements ShouldQueue
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

        $columns = [
            'id' => 'Paper ID',
            'department_name' => 'Department',
            'course_name' => 'Course',
            'semester' => 'Semester',
            'code' => 'Paper Code',
            'name' => 'Paper Name',
            'paper_type' => 'Paper Type',
            'status' => 'Status',
            'number_of_lectures' => 'Credit Of Lectures',
            'number_of_tutorials' => 'Credit Of Tutorials',
            'number_of_practicals' => 'Credit Of Practicals',
            'eligibilty' => 'Eligibility',
            'prerequisites' => 'Prerequisites',
            'capping' => 'Capping',
            'remark' => 'Remark',
            'student_count' => 'Number Of Students',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Papers');

        foreach (array_values($columns) as $index => $heading) {
            $sheet->setCellValueExplicitByColumnAndRow($index + 1, 1, $heading, DataType::TYPE_STRING);
            $sheet->getColumnDimensionByColumn($index + 1)->setWidth(in_array($heading, ['Paper Name', 'Eligibility', 'Prerequisites', 'Remark'], true) ? 36 : 20);
        }

        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        $sheet->freezePane('A2');

        $rowNumber = 2;

        $this->papersQuery()
            ->orderBy('p.id')
            ->chunkById(200, function ($papers) use ($sheet, $columns, &$processed, &$rowNumber, $key) {
                foreach ($papers as $paper) {
                    foreach (array_keys($columns) as $index => $column) {
                        $sheet->setCellValueExplicitByColumnAndRow(
                            $index + 1,
                            $rowNumber,
                            (string) ($paper->{$column} ?? ''),
                            DataType::TYPE_STRING
                        );
                    }

                    $rowNumber++;
                    $processed++;
                    if ($processed % 10 === 0 || $processed === $this->total) {
                        $this->updateProgress($key, $processed, 'generating');
                    }
                }
            }, 'p.id', 'id');

        $lastRow = $rowNumber - 1;
    $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

        $path = 'exports/papers_'.$this->token.'.xlsx';
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
        Storage::disk('local')->delete('exports/papers_'.$this->token.'.xlsx');
    }

    private function papersQuery()
    {
        return DB::table('paper_master as p')
            ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
            ->leftJoin('courses as c', 'c.id', '=', 'p.course_id')
            ->leftJoinSub(
                DB::table('student_papers')
                    ->selectRaw('paper_master_id, COUNT(*) as student_count')
                    ->groupBy('paper_master_id'),
                'student_counts',
                fn ($join) => $join->on('student_counts.paper_master_id', '=', 'p.id')
            )
            ->select([
                'p.id', 'd.name as department_name',
                'c.name as course_name', 'p.semester',
                'p.code', 'p.name', 'p.paper_type', 'p.status',
                'p.number_of_lectures', 'p.number_of_tutorials', 'p.number_of_practicals',
                'p.eligibilty', 'p.prerequisites', 'p.capping', 'p.remark',
                DB::raw('COALESCE(student_counts.student_count, 0) as student_count'),
                'p.created_at', 'p.updated_at',
            ])
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('p.name', 'like', "%{$search}%")
                        ->orWhere('p.code', 'like', "%{$search}%")
                        ->orWhere('d.name', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['department_id'] ?? null, fn ($query, $value) => $query->where('p.dept_id', $value))
            ->when($this->filters['course_id'] ?? null, fn ($query, $value) => $query->where('p.course_id', $value))
            ->when($this->filters['semester'] ?? null, fn ($query, $value) => $query->where('p.semester', $value))
            ->when(($this->filters['semester_filter'] ?? null) === 'even', fn ($query) => $query->whereRaw('MOD(CAST(p.semester AS UNSIGNED), 2) = 0'))
            ->when(($this->filters['semester_filter'] ?? null) === 'odd', fn ($query) => $query->whereRaw('MOD(CAST(p.semester AS UNSIGNED), 2) != 0'))
            ->when($this->filters['paper_type'] ?? null, fn ($query, $value) => $query->where('p.paper_type', $value))
            ->when($this->filters['status'] ?? null, fn ($query, $value) => $query->where('p.status', $value));
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
        return 'papers_export_'.$this->token;
    }
}
