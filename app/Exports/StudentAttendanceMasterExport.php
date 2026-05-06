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

    /* ================= HEADINGS ================= */
    public function headings(): array
    {
        return [
            [
                'Student Info','','','','','',
                'Lecture','','',
                'Tutorial','','',
                'Practical','','',
                'Total','',''
            
            ],
            [
                'Student','Department','Course','Semester','Section','Paper',

                'Held','Attended','%',
                'Held','Attended','%',
                'Held','Attended','%',
                'Held','Attended','%'
                
            ]
        ];
    }

    /* ================= HELPER ================= */
    private function percent($present, $working)
    {
        return $working ? round(($present / $working) * 100, 2) : 0;
    }

    /* ================= DATA ================= */
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
            ->when($this->breakup, fn($q) => $q->where('sa.month', $this->month))
            ->when(!$this->breakup, fn($q) => $q->where('sa.month', '>=', $this->month))
            ->orderBy('s.name')
            ->get()
            ->groupBy('student_id');

        foreach ($data as $studentRows) {

            $first = $studentRows->first();

            /* ===== TOTALS ===== */
            $lecHeld = $studentRows->sum('lecture_working_days');
            $lecAtt = $studentRows->sum('lecture_present_days');

            $tutHeld = $studentRows->sum('tute_working_days');
            $tutAtt = $studentRows->sum('tute_present_days');

            $pracHeld = $studentRows->sum('practical_working_days');
            $pracAtt = $studentRows->sum('practical_present_days');

            $tch = $lecHeld + $tutHeld + $pracHeld;
            $tca = $lecAtt + $tutAtt + $pracAtt;

            /* ===== PERCENTAGES ===== */
            $lec = $this->percent($lecAtt, $lecHeld);
            $tut = $this->percent($tutAtt, $tutHeld);
            $prac = $this->percent($pracAtt, $pracHeld);
            $overall = $this->percent($tca, $tch);

            /* ===== SUMMARY ROW ===== */
            $rows->push([
                $first->student_name . ' (' . $first->roll_number . ')',
                $first->department_name,
                $first->course_name,
                $first->semester_id,
                $first->section,
                $this->breakup ? 'STUDENT SUMMARY' : 'OVERALL SUMMARY',

                // Lecture
                $lecHeld, $lecAtt, $lec,

                // Tutorial
                $tutHeld, $tutAtt, $tut,

                // Practical
                $pracHeld, $pracAtt, $prac,

                // Total
                $tch, $tca,$overall,

                // Overall %
                
            ]);

            /* ===== PAPER ROWS ===== */
            if ($this->breakup) {

                foreach ($studentRows as $r) {

                    $ptch = ($r->lecture_working_days ?? 0)
                        + ($r->tute_working_days ?? 0)
                        + ($r->practical_working_days ?? 0);

                    $ptca = ($r->lecture_present_days ?? 0)
                        + ($r->tute_present_days ?? 0)
                        + ($r->practical_present_days ?? 0);

                    $rows->push([
                        ' → ' . $r->paper_name,
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

                        $ptch, $ptca, ' ',
                    ]);
                }

                // spacer row
                $rows->push(array_fill(0, 18, ''));
            }
        }

        return $rows;
    }

    /* ================= STYLES ================= */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    /* ================= EVENTS ================= */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->freezePane('A3');

                /* ===== MERGE ===== */
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('G1:I1');
                $sheet->mergeCells('J1:L1');
                $sheet->mergeCells('M1:O1');
                $sheet->mergeCells('P1:R1'); // Total

                /* ===== HEADER STYLE ===== */
                $sheet->getStyle('A1:R1')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '02317C']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A2:R2')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'D9E1F2']],
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                /* ===== % COLORING ===== */
                foreach (range(3, $highestRow) as $row) {
                    foreach (['I', 'L', 'O', 'R'] as $col) {

                        $value = $sheet->getCell("$col$row")->getValue();

                        if (is_numeric($value)) {
                            $color = $value >= 75 ? 'C6EFCE'
                                : ($value >= 50 ? 'FFEB9C' : 'FFC7CE');

                            $sheet->getStyle("$col$row")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $color]],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        }
                    }
                }

                /* ===== BORDERS ===== */
                $sheet->getStyle("A1:R{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
            },
        ];
    }
}