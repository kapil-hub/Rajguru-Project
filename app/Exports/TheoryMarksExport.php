<?php

namespace App\Exports;

use App\Models\Paper;
use App\Models\StudentTheoryMark;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TheoryMarksExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private readonly int $paperId,
        private readonly int $teacherId
    ) {
    }

    public function collection()
    {
        $eligiblePaperIds = Paper::query()
            ->eligibleForTheoryMarksByCoordinator($this->teacherId)
            ->whereKey($this->paperId)
            ->select('id');

        return StudentTheoryMark::query()
            ->with(['student.academic', 'paper.course'])
            ->where('teacher_id', $this->teacherId)
            ->where('paper_id', $this->paperId)
            ->whereIn('paper_id', $eligiblePaperIds)
            ->get()
            ->map(function (StudentTheoryMark $mark) {
                return [
                    'Student Name' => $mark->student?->name ?? '',
                    'Exam Roll Number' => $mark->student?->academic?->roll_number ?? '',
                    'College Roll Number' => $mark->student?->academic?->college_roll_number ?? '',
                    'Paper Code' => $mark->paper?->code ?? '',
                    'Paper Name' => $mark->paper?->name ?? '',
                    'Program Code' => $mark->paper?->course?->program_code ?? '',
                    'Semester' => $mark->paper?->semester ?? '',
                    'Maximum Theory Marks' => lectureMarksBreakup(
                        (int) ($mark->paper?->number_of_lectures ?? 0)
                    )['theory'],
                    'Obtained Theory Marks' => $mark->theory_marks,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Exam Roll Number',
            'College Roll Number',
            'Paper Code',
            'Paper Name',
            'Program Code',
            'Semester',
            'Maximum Theory Marks',
            'Obtained Theory Marks',
        ];
    }
}
