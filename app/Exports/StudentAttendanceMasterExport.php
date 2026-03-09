<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentAttendanceMasterExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings
{
    protected $month;

    protected $year;

    protected $breakup;

    public function __construct($month, $year, $breakup)
    {
        $this->month = $month;
        $this->year = $year;
        $this->breakup = $breakup;
    }

    /*
    |--------------------------------------------------------------------------
    | HEADERS
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            [
                'Student Info', '', '', '', '', '',
                'Lecture', '', '',
                'Tutorial', '', '',
                'Practical', '', '',
                'Overall',
            ],
            [
                'Student',
                'Department',
                'Course',
                'Semester',
                'Section',
                'Paper',

                'Classes Held',
                'Classes Attended',
                '%',

                'Classes Held',
                'Classes Attended',
                '%',

                'Classes Held',
                'Classes Attended',
                '%',

                '%',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PERCENTAGE HELPER
    |--------------------------------------------------------------------------
    */

    private function percent($present, $working)
    {
        if (! $working) {
            return 0;
        }

        return round(($present / $working) * 100, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    public function collection()
    {
        $rows = collect();

        $data = DB::table('student_attendances as sa')
            ->join('student_users as s', 's.id', '=', 'sa.student_id')
            ->join('student_academic as sc', 'sc.student_user_id', '=', 'sa.student_id')
            ->join('departments as d', 'd.id', '=', 'sc.department_id')
            ->join('paper_master as p', 'p.id', '=', 'sa.paper_master_id')
            ->join('courses as c', 'c.id', '=', 'sc.course_id')
            ->select(
                'sa.student_id',
                's.name as student_name',
                'sc.roll_number',
                'c.name as course_name',
                'd.name as department_name',
                'sa.semester_id',
                'sa.section',
                'p.name as paper_name',

                'sa.lecture_working_days',
                'sa.lecture_present_days',

                'sa.tute_working_days',
                'sa.tute_present_days',

                'sa.practical_working_days',
                'sa.practical_present_days'
            )
           // month wise report
            ->when($this->breakup, function ($q) {
                $q->where('sa.month', $this->month);
            })

            // complete report
            ->when(! $this->breakup, function ($q) {
                $q->where('sa.month', '>=', $this->month);
            })

            ->orderBy('s.name')
            ->get()
            ->groupBy('student_id');

        foreach ($data as $studentRows) {

            $first = $studentRows->first();

            /*
            |--------------------------------------------------------------------------
            | STUDENT SUMMARY
            |--------------------------------------------------------------------------
            */

            $lec = $this->percent(
                $studentRows->sum('lecture_present_days'),
                $studentRows->sum('lecture_working_days')
            );

            $tut = $this->percent(
                $studentRows->sum('tute_present_days'),
                $studentRows->sum('tute_working_days')
            );

            $prac = $this->percent(
                $studentRows->sum('practical_present_days'),
                $studentRows->sum('practical_working_days')
            );

            $values = collect([$lec, $tut, $prac])->filter(fn ($v) => $v > 0);

            $overall = $values->count()
                ? round($values->avg(), 2)
                : 0;

            /*
            |--------------------------------------------------------------------------
            | SUMMARY ROW
            |--------------------------------------------------------------------------
            */

            $rows->push([
                $first->student_name.' ('.$first->roll_number.')',
                $first->department_name,
                $first->course_name,
                $first->semester_id,
                $first->section,
                $this->breakup ? 'STUDENT SUMMARY' : 'OVERALL SUMMARY',

                '', '', $lec,
                '', '', $tut,
                '', '', $prac,
                $overall,
            ]);

            /*
            |--------------------------------------------------------------------------
            | PAPER ROWS (ONLY FOR MONTH REPORT)
            |--------------------------------------------------------------------------
            */

            if ($this->breakup) {

                foreach ($studentRows as $r) {

                    $rows->push([
                        '   → '.$r->paper_name,
                        '', '', '', '', '',

                        $r->lecture_working_days ?? 0,
                        $r->lecture_present_days ?? 0,
                        $this->percent($r->lecture_present_days, $r->lecture_working_days),

                        $r->tute_working_days ?? 0,
                        $r->tute_present_days ?? 0,
                        $this->percent($r->tute_present_days, $r->tute_working_days),

                        $r->practical_working_days ?? 0,
                        $r->practical_present_days ?? 0,
                        $this->percent($r->practical_present_days, $r->practical_working_days),

                        '',
                    ]);
                }
            }

            if ($this->breakup) {
                $rows->push(array_fill(0, 16, ''));
            }
        }

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | BASIC HEADER STYLE
    |--------------------------------------------------------------------------
    */

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | EXCEL FORMATTING
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->freezePane('A3');

                /*
                | Header Merge
                */

                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('G1:I1');
                $sheet->mergeCells('J1:L1');
                $sheet->mergeCells('M1:O1');
                $sheet->mergeCells('P1:P2');

                /*
                | Header Style
                */

                $sheet->getStyle('A1:P1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '02317C'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A2:P2')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'D9E1F2'],
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                /*
                | Percentage Coloring
                */

                for ($row = 3; $row <= $highestRow; $row++) {

                    foreach (['I', 'L', 'O', 'P'] as $col) {

                        $value = $sheet->getCell("$col$row")->getValue();

                        if (is_numeric($value)) {

                            if ($value >= 75) {
                                $color = 'C6EFCE';
                            } elseif ($value >= 50) {
                                $color = 'FFEB9C';
                            } else {
                                $color = 'FFC7CE';
                            }

                            $sheet->getStyle("$col$row")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'color' => ['rgb' => $color],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                ],
                            ]);
                        }
                    }
                }

                /*
                | Borders
                */

                $sheet->getStyle("A1:P{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
            },
        ];
    }
}
