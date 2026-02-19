<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentAttendanceMasterExport implements
    FromCollection,
    WithHeadings,
    WithEvents,
    ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year  = $year;
    }

    /**
     * TWO ROW HEADERS
     */
    public function headings(): array
    {
        return [
            [
                'Student Info', '','', '', '', '',
                'Lecture', '', '',
                'Tutorial', '', '',
                'Practical', '', '',
                'Overall'
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

                '%'
            ]
        ];
    }

    /**
     * DATA
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
            'sc.roll_number as roll_no',
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
        ->where('sa.month', $this->month)
        ->where('sa.year', $this->year)
        ->orderBy('s.name')
        ->get()
        ->groupBy('student_id');

    foreach ($data as $studentRows) {

        $first = $studentRows->first();

        // ===== STUDENT SUMMARY =====
        $lec = round($studentRows->avg(function ($r) {
            if (is_null($r->lecture_working_days)) {
                return null;   
            }

            if ($r->lecture_working_days == 0) {
                return 0;      
            }
            return $r->lecture_working_days > 0
                ? ($r->lecture_present_days / $r->lecture_working_days) * 100
                : 0;
        }), 2);

        $tut = round($studentRows->avg(function ($r) {
            if (is_null($r->tute_working_days)) {
                return null;   
            }

            if ($r->tute_working_days == 0) {
                return 0;      
            }
            return $r->tute_working_days > 0
                ? ($r->tute_present_days / $r->tute_working_days) * 100
                : 0;
        }), 2);

        $prac = round($studentRows->avg(function ($r) {
            if (is_null($r->practical_working_days)) {
                return null;   
            }

            if ($r->practical_working_days == 0) {
                return 0;      
            }
            return $r->practical_working_days > 0
                ? ($r->practical_present_days / $r->practical_working_days) * 100
                : 0;
        }), 2);

        $devide_by = 0;
        if($lec > 0){$devide_by++;}
        if($tut > 0){$devide_by++;}
        if($prac > 0){$devide_by++;}
        if($devide_by == 0){ $devide_by = 1;}
        // 🔹 MAIN STUDENT ROW
        $rows->push([
            $first->student_name . ' (' . $first->roll_no . ')',
            $first->department_name,
            $first->course_name, 
            $first->semester_id,
            $first->section,
            'STUDENT SUMMARY',

            '', '', $lec,
            '', '', $tut,
            '', '', $prac,
            round(($lec + $tut + $prac) / $devide_by, 2),
        ]);

        // 🔹 PAPER ROWS
        foreach ($studentRows as $r) {

            $lecP = $r->lecture_working_days > 0
                ? round(($r->lecture_present_days / $r->lecture_working_days) * 100, 2)
                : 0;

            $tutP = $r->tute_working_days > 0
                ? round(($r->tute_present_days / $r->tute_working_days) * 100, 2)
                : 0;

            $pracP = $r->practical_working_days > 0
                ? round(($r->practical_present_days / $r->practical_working_days) * 100, 2)
                : 0;

            $rows->push([
                '   → ' . $r->paper_name,
                '', '', '', '','',

                $r->lecture_working_days ?? 0,$r->lecture_present_days ?? 0, $lecP,
                $r->tute_working_days ?? 0,$r->tute_present_days ?? 0, $tutP,
                $r->practical_working_days ?? 0,$r->practical_present_days ?? 0, $pracP,
                '',
            ]);
        }

        // 🔹 Spacer row
        $rows->push(array_fill(0, 15, ''));
    }

    return $rows;
}


    /**
     * BASIC STYLES
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

    /**
     * ADVANCED STYLING & MERGING
     */
    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {

            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();

            // Freeze header
            $sheet->freezePane('A3');

            // Header Styling
            $sheet->getStyle('A1:P1')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '02317C']
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            $sheet->getStyle('A2:P2')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D9E1F2']
                ],
                'font' => [
                    'bold' => true
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Loop rows for coloring
            for ($row = 3; $row <= $highestRow; $row++) {

                $studentCell = $sheet->getCell("F$row")->getValue();

                // ===== STUDENT SUMMARY ROW =====
                if ($studentCell === 'STUDENT SUMMARY') {

                    $sheet->getStyle("A$row:P$row")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'BDD7EE'] // Blue
                        ],
                        'font' => [
                            'bold' => true
                        ]
                    ]);
                }

                // ===== PERCENTAGE COLORING =====
                foreach (['I','L','O','P'] as $col) {

                    $value = $sheet->getCell("$col$row")->getValue();

                    if (is_numeric($value)) {

                        if ($value >= 75) {
                            $color = 'C6EFCE'; // Green
                        } elseif ($value >= 50) {
                            $color = 'FFEB9C'; // Yellow
                        } else {
                            $color = 'FFC7CE'; // Red
                        }

                        $sheet->getStyle("$col$row")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => $color]
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);
                    }
                }
            }

            // Full Table Borders
            $sheet->getStyle("A1:P{$highestRow}")
                ->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
        }
    ];
}

}
