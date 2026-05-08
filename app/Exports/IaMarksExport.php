<?php

namespace App\Exports;

use App\Models\IaMark;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class IaMarksExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $paperMamsterID;
    protected $semester;

    public function __construct($paperMamsterID, $semester)
    {
        $this->paperMamsterID = $paperMamsterID;
        $this->semester = $semester;
    }

    public function collection()
    {
        return IaMark::with([
                'student',
                'student.academic',
                'paper',
                'course'
            ])
            ->whereIn('paper_master_id', $this->paperMamsterID)
            ->where('semester_id', $this->semester)
            ->get()
            ->map(function ($mark) {

                return [
                    'Student Name'        => $mark->student->name ?? '',
                    'Exam Roll Number'   => $mark->student->academic->roll_number ?? '',
                    'Paper Code'         => $mark->paper->code ?? '',
                    'Paper Name'         => $mark->paper->name ?? '',
                    'Semester'           => $mark->semester_id ?? '',
                    'Program Code'       => $mark->course->program_code ?? '',
                    'Program Name'       => $mark->course->name ?? '',
                    'Maximum Marks (IA)' => lectureMarksBreakup($mark->paper->number_of_lectures ?? 0)['ia'] ?? 0,
                    'Obtained Marks (IA)' => round($mark->total) ?? 0,
                    'Maximum Marks (Tutorial)' =>tutorialMarksBreakup($mark->paper->number_of_tutorials ?? 0)['total_tute'] ?? 0,
                    'Obtained Marks (Tutorial)' => round($mark->tute_ca + $mark->tute_attendance) ?? 0,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Exam Roll Number',
            'Paper Code',
            'Paper Name',
            'Semester',
            'Program Code',
            'Program Name',
            'Maximum Marks (IA)',
            'Obtained Marks (IA)',
            'Maximum Marks (Tutorial)',
            'Obtained Marks (Tutorial)'
        ];
    }
}