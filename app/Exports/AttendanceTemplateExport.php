<?php 


namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AttendanceTemplateExport implements WithEvents
{
    protected array $students;
    protected $lectureWD;
    protected $tuteWD;
    protected $practicalWD;

    public function __construct(array $students, $lectureWD, $tuteWD, $practicalWD)
    {
        $this->students     = $students;
        $this->lectureWD    = $lectureWD;
        $this->tuteWD       = $tuteWD;
        $this->practicalWD = $practicalWD;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /* ===============================
                   BASE HEADERS
                =============================== */
                $sheet->mergeCells('A1:A2')->setCellValue('A1', 'Roll Number');
                $sheet->mergeCells('B1:B2')->setCellValue('B1', 'Student Name');

                $currentCol = 'C';

                $columns = [];

                /* ===============================
                   LECTURE
                =============================== */
                if ($this->lectureWD !== 'hidden') {
                    $sheet->mergeCells("{$currentCol}1:" . chr(ord($currentCol) + 1) . "1")
                        ->setCellValue("{$currentCol}1", 'Lecture');

                    $sheet->setCellValue("{$currentCol}2", 'Classes Held');
                    $sheet->setCellValue(chr(ord($currentCol) + 1) . '2', 'Classes Attended');

                    $columns['lecture'] = [
                        'wd' => $currentCol,
                        'p'  => chr(ord($currentCol) + 1),
                    ];

                    $currentCol = chr(ord($currentCol) + 2);
                }

                /* ===============================
                   TUTE
                =============================== */
                if ($this->tuteWD !== 'hidden') {
                    $sheet->mergeCells("{$currentCol}1:" . chr(ord($currentCol) + 1) . "1")
                        ->setCellValue("{$currentCol}1", 'Tutorial');

                    $sheet->setCellValue("{$currentCol}2", 'Classes Held');
                    $sheet->setCellValue(chr(ord($currentCol) + 1) . '2', 'Classes Attended');

                    $columns['tutorial'] = [
                        'wd' => $currentCol,
                        'p'  => chr(ord($currentCol) + 1),
                    ];

                    $currentCol = chr(ord($currentCol) + 2);
                }

                /* ===============================
                   PRACTICAL
                =============================== */
                if ($this->practicalWD !== 'hidden') {
                    $sheet->mergeCells("{$currentCol}1:" . chr(ord($currentCol) + 1) . "1")
                        ->setCellValue("{$currentCol}1", 'Practical');

                    $sheet->setCellValue("{$currentCol}2", 'Classes Held');
                    $sheet->setCellValue(chr(ord($currentCol) + 1) . '2', 'Classes Attended');

                    $columns['practical'] = [
                        'wd' => $currentCol,
                        'p'  => chr(ord($currentCol) + 1),
                    ];

                    $currentCol = chr(ord($currentCol) + 2);
                }

                /* ===============================
                   STYLE HEADERS
                =============================== */
                $sheet->getStyle("A1:{$currentCol}2")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '4F46E5'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);

                foreach (range('A', $currentCol) as $col) {
                    $sheet->getColumnDimension($col)->setWidth(20);
                }

                /* ===============================
                   FILL STUDENT ROWS
                =============================== */
                $startRow = 3;
                $row = $startRow;

                foreach ($this->students as $i => $student) {

                    $sheet->setCellValue("A{$row}", $student["academic"]['roll_number'] ?? "N/A");
                    $sheet->setCellValue("B{$row}", $student['name']);

                    if (isset($columns['lecture'])) {
                        $sheet->setCellValue($columns['lecture']['wd'] . $row, $this->lectureWD);
                    }

                    if (isset($columns['tutorial'])) {
                        $sheet->setCellValue($columns['tutorial']['wd'] . $row, $this->tuteWD);
                    }

                    if (isset($columns['practical'])) {
                        $sheet->setCellValue($columns['practical']['wd'] . $row, $this->practicalWD);
                    }

                    // Hidden student_id column (after last visible column)
                    $sheet->setCellValue($currentCol . $row, $student['id']);

                    $row++;
                }

                $lastRow = $row - 1;

                /* ===============================
                   HIDE STUDENT ID COLUMN
                =============================== */
                $sheet->getColumnDimension($currentCol)->setVisible(false);

                /* ===============================
                   PROTECTION
                =============================== */
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword('attendance');

                // Lock headers
                $sheet->getStyle("A1:{$currentCol}2")->getProtection()->setLocked(true);

                // Lock fixed columns
                $sheet->getStyle("A{$startRow}:B{$lastRow}")->getProtection()->setLocked(true);
                $sheet->getStyle("{$currentCol}{$startRow}:{$currentCol}{$lastRow}")->getProtection()->setLocked(true);

                // Lock WD, Unlock P
                foreach ($columns as $set) {
                    $sheet->getStyle($set['wd'] . "{$startRow}:" . $set['wd'] . "{$lastRow}")
                          ->getProtection()->setLocked(false);

                    $sheet->getStyle($set['p'] . "{$startRow}:" . $set['p'] . "{$lastRow}")
                          ->getProtection()->setLocked(false);
                }

                /* ===============================
                   FREEZE HEADER
                =============================== */
                $sheet->freezePane('A3');
            }
        ];
    }
}
