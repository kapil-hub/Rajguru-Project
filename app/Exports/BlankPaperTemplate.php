<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class BlankPaperTemplate implements
    FromArray,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Department Name',
            'Course Name',
            'Semester',
            'Paper Code',
            'Paper Name',
            'Paper Type (CORE / ELECTIVE)',
            'Status (Active / Inactive)',
            'Credit Of Lectures',
            'Credit Of Tutorials',
            'Credit Of Practicals'
        ];
    }

    public function array(): array
    {
        return []; // Blank template
    }

    public function styles(Worksheet $sheet)
    {
        /** 🔒 Lock header row */
        $sheet->getStyle('A1:J1')->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED);

        /** 🔓 Unlock all other rows for data entry */
        $sheet->getStyle('A2:J1000')->getProtection()
            ->setLocked(Protection::PROTECTION_UNPROTECTED);

        /** 🔐 Protect the sheet */
        $sheet->getProtection()
            ->setSheet(true)
            ->setPassword('readonly'); // optional password

        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
