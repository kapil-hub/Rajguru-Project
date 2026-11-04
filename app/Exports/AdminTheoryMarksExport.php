<?php

namespace App\Exports;

use App\Models\StudentTheoryMark;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminTheoryMarksExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private readonly string $departmentId = '',
        private readonly string $courseId = '',
        private readonly string $semester = '',
        private readonly string $paperType = '',
        private readonly string $paperId = '',
        private readonly string $teacherId = ''
    ) {
    }

    public function collection()
    {
        return $this->query()
            ->get()
            ->map(function (StudentTheoryMark $mark) {
                return [
                    'Department' => $mark->paper?->course?->department?->name ?? '',
                    'Course' => $mark->paper?->course?->name ?? '',
                    'Student Name' => $mark->student?->name ?? '',
                    'Exam Roll Number' => $mark->student?->academic?->roll_number ?? '',
                    'College Roll Number' => $mark->student?->academic?->college_roll_number ?? '',
                    'Paper Code' => $mark->paper?->code ?? '',
                    'Paper Name' => $mark->paper?->name ?? '',
                    'Semester' => $mark->paper?->semester ?? '',
                    'Teacher' => $mark->teacher?->name ?? '',
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
            'Department',
            'Course',
            'Student Name',
            'Exam Roll Number',
            'College Roll Number',
            'Paper Code',
            'Paper Name',
            'Semester',
            'Teacher',
            'Maximum Theory Marks',
            'Obtained Theory Marks',
        ];
    }

    private function query(): Builder
    {
        return StudentTheoryMark::query()
            ->with(['student.academic', 'paper.course.department', 'teacher'])
            ->whereHas('paper', function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->whereIn('paper_type', ['SEC', 'VAC', 'AEC'])
                            ->where('number_of_lectures', 1)
                            ->where('number_of_tutorials', 0)
                            ->where('number_of_practicals', 1);
                    })->orWhere(function (Builder $query) {
                        $query->where('paper_type', 'AEC')
                            ->where('number_of_lectures', 2)
                            ->where('number_of_tutorials', 0)
                            ->where('number_of_practicals', 0);
                    });
                });
            })
            ->when($this->departmentId, fn (Builder $query) => $query
                ->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('dept_id', $this->departmentId)))
            ->when($this->courseId, fn (Builder $query) => $query
                ->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('course_id', $this->courseId)))
            ->when($this->semester, fn (Builder $query) => $query
                ->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('semester', $this->semester)))
            ->when($this->paperType, fn (Builder $query) => $query
                ->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('paper_type', $this->paperType)))
            ->when($this->paperId, fn (Builder $query) => $query
                ->where('paper_id', $this->paperId))
            ->when($this->teacherId, fn (Builder $query) => $query
                ->where('teacher_id', $this->teacherId))
            ->orderByDesc('id');
    }
}
