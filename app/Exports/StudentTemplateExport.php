<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromArray,
    WithEvents,
    WithColumnWidths
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use App\Models\Departments;

class StudentTemplateExport implements FromArray, WithEvents, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                // ===== Student Info =====
                'Control Number',
                'Student Name',
                'Email',
                'Mobile',
                'Admission Academic Year',
                'Roll Number',
                'Department',
                'Course',
                'Current Semester',
                'Section',
                'Current Academic Year',
                'Father Name',
                'Mother Name',
                'Parents Contact',
                'Parents Email',

                // ===== Paper 1 =====
                'Paper 1 Code', 'Paper 1 Type', 'Paper 1 Name',
                // ===== Paper 2 =====
                'Paper 2 Code', 'Paper 2 Type', 'Paper 2 Name',
                // ===== Paper 3 =====
                'Paper 3 Code', 'Paper 3 Type', 'Paper 3 Name',
                // ===== Paper 4 =====
                'Paper 4 Code', 'Paper 4 Type', 'Paper 4 Name',
                // ===== Paper 5 =====
                'Paper 5 Code', 'Paper 5 Type', 'Paper 5 Name',
                // ===== Paper 6 =====
                'Paper 6 Code', 'Paper 6 Type', 'Paper 6 Name',
                // ===== Paper 7 =====
                'Paper 7 Code', 'Paper 7 Type', 'Paper 7 Name',
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 25, 'C' => 30, 'D' => 18, 'E' => 24,
            'F' => 15, 'G' => 24, 'H' => 28, 'I' => 18, 'J' => 12,
            'K' => 22, 'L' => 22, 'M' => 22, 'N' => 22, 'O' => 26,

            // Paper columns
            'P' => 18, 'Q' => 18, 'R' => 30,
            'S' => 18, 'T' => 18, 'U' => 30,
            'V' => 18, 'W' => 18, 'X' => 30,
            'Y' => 18, 'Z' => 18, 'AA' => 30,
            'AB' => 18, 'AC' => 18, 'AD' => 30,
            'AE' => 18, 'AF' => 18, 'AG' => 30,
            'AH' => 18, 'AI' => 18, 'AJ' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();

                $spreadsheet->getDefaultStyle()
                    ->getProtection()
                    ->setLocked(Protection::PROTECTION_UNPROTECTED);

      
                $sheet->getStyle('A1:AJ1')
                    ->getProtection()
                    ->setLocked(Protection::PROTECTION_PROTECTED);

      
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword('locked');
                $sheet->getStyle('A1:AJ1')->getFont()->setBold(true);
                $sheet->freezePane('A2');

    
                $masterSheet = new Worksheet($spreadsheet, 'master');
                $spreadsheet->addSheet($masterSheet);
                $masterSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                $departments = Departments::with('courses')->get();
                $row = 1;

                foreach ($departments as $dept) {
         
                    $masterSheet->setCellValue("A{$row}", $dept->name);

                    $col = 2; // B
                    foreach ($dept->courses as $course) {
                        $masterSheet->setCellValueByColumnAndRow($col, $row, $course->name);
                        $col++;
                    }

                    $safeDeptName = preg_replace('/[^A-Za-z0-9_]/', '_', $dept->name);
                    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);
                    $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
                        $safeDeptName,
                        $masterSheet,
                        "B{$row}:{$lastCol}{$row}"
                    ));

                    $row++;
                }

                $lastDeptRow = $row - 1;

    
                $deptValidation = new DataValidation();
                $deptValidation->setType(DataValidation::TYPE_LIST);
                $deptValidation->setAllowBlank(false);
                $deptValidation->setShowDropDown(true);
                $deptValidation->setErrorStyle(DataValidation::STYLE_STOP);
                $deptValidation->setFormula1("=master!\$A\$1:\$A\${$lastDeptRow}");
                $sheet->setDataValidation("G2:G1000", $deptValidation);

          
                $courseValidationTemplate = new DataValidation();
                $courseValidationTemplate->setType(DataValidation::TYPE_LIST);
                $courseValidationTemplate->setAllowBlank(false);
                $courseValidationTemplate->setShowDropDown(true);
                $courseValidationTemplate->setErrorStyle(DataValidation::STYLE_STOP);

                for ($i = 2; $i <= 1000; $i++) {
                    $cv = clone $courseValidationTemplate;
                    $cv->setFormula1("=INDIRECT(SUBSTITUTE(G{$i},\" \",\"_\"))");
                    $sheet->setDataValidation("H{$i}", $cv);
                }

                $semesterValidation = new DataValidation();
                $semesterValidation->setType(DataValidation::TYPE_LIST);
                $semesterValidation->setFormula1('"1,2,3,4,5,6,7,8"');
                $semesterValidation->setAllowBlank(false);
                $sheet->setDataValidation("I2:I1000", $semesterValidation);


                $paperTypeValidationTemplate = new DataValidation();
                $paperTypeValidationTemplate->setType(DataValidation::TYPE_LIST);
                $paperTypeValidationTemplate->setFormula1('"Theory,Practical,Tutorial"');
                $paperTypeValidationTemplate->setAllowBlank(true);

                $paperTypeColumns = ['Q','T','W','Z','AC','AF','AI'];
                foreach ($paperTypeColumns as $col) {
                    $sheet->setDataValidation("{$col}2:{$col}1000", clone $paperTypeValidationTemplate);
                }

            }
        ];
    }
}
