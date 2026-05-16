<?php

namespace App\Exports;

use App\Models\StudentPracticalMark;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PracticalMarksExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $paperMamsterID;
    protected $semester;

    public function __construct($paperMamsterID)
    {
        $this->paperMamsterID = $paperMamsterID;
    }

    public function collection()
    {
        return StudentPracticalMark::with([
                'student',
                'student.academic',
                'paper',
                'course'
            ])
            ->whereIn('paper_id', $this->paperMamsterID)
            ->get()
            ->map(function ($mark) {

                return [
                    'Student Name'        => $mark->student->name ?? '',
                    'Exam Roll Number'   => $mark->student->academic->roll_number ?? '',
                    'Paper Code'         => $mark->paper->code ?? '',
                    'Paper Name'         => $mark->paper->name ?? '',
                    'Program Code'       => $mark->paper->course->program_code ?? '',
                    'Program Name'       => $mark->paper->course->name ?? '',
                    'Semester'           => $mark->paper->semester ?? '',
                    'Maximum Marks (PR)' => practicalMarksBreakup($mark->paper->number_of_practicals ?? 0)['total'] ?? 0,
                    'Obtained Marks (PR)' => round($mark->total_marks) ?? 0,
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
            'Program Code',
            'Program Name',
            'Semester',
            'Maximum Marks (PR)',
            'Obtained Marks (PR)'
        ];
    }
}