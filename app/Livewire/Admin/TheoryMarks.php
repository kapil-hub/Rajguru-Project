<?php

namespace App\Livewire\Admin;

use App\Exports\AdminTheoryMarksExport;
use App\Models\Courses;
use App\Models\Departments;
use App\Models\Paper;
use App\Models\StudentTheoryMark;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class TheoryMarks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selectedDepartment = '';
    public $selectedCourse = '';
    public $selectedSemester = '';
    public $selectedPaperType = '';
    public $selectedPaper = '';
    public $selectedTeacher = '';

    public $departments = [];
    public $courses = [];
    public $semesters = [
        'I' => 1,
        'II' => 2,
        'III' => 3,
        'IV' => 4,
        'V' => 5,
        'VI' => 6,
        'VII' => 7,
        'VIII' => 8,
    ];
    public $paperTypes = ['SEC', 'VAC', 'AEC'];

    public function mount(): void
    {
        $this->departments = Departments::query()->orderBy('name')->get();
        $this->courses = collect();
    }

    public function updatedSelectedDepartment(): void
    {
        $this->selectedCourse = '';
        $this->selectedPaper = '';
        $this->courses = $this->selectedDepartment
            ? Courses::query()->where('dept_id', $this->selectedDepartment)->orderBy('name')->get()
            : collect();
        $this->resetPage();
    }

    public function updatedSelectedCourse(): void
    {
        $this->selectedPaper = '';
        $this->resetPage();
    }

    public function updatedSelectedSemester(): void
    {
        $this->selectedPaper = '';
        $this->resetPage();
    }

    public function updatedSelectedPaperType(): void
    {
        $this->selectedPaper = '';
        $this->resetPage();
    }

    public function updatedSelectedPaper(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedTeacher(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'selectedDepartment',
            'selectedCourse',
            'selectedSemester',
            'selectedPaperType',
            'selectedPaper',
            'selectedTeacher',
        ]);
        $this->courses = collect();
        $this->resetPage();
    }

    public function exportExcel()
    {
        return Excel::download(
            new AdminTheoryMarksExport(
                $this->selectedDepartment,
                $this->selectedCourse,
                $this->selectedSemester,
                $this->selectedPaperType,
                $this->selectedPaper,
                $this->selectedTeacher
            ),
            'theory-marks.xlsx'
        );
    }

    private function marksQuery(): Builder
    {
        return StudentTheoryMark::query()
            ->with(['student.academic', 'paper.course.department', 'teacher'])
            ->whereHas('paper', fn (Builder $query) => $this->applyEligiblePaperCriteria($query))
            ->when($this->selectedDepartment, function (Builder $query) {
                $query->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('dept_id', $this->selectedDepartment));
            })
            ->when($this->selectedCourse, function (Builder $query) {
                $query->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('course_id', $this->selectedCourse));
            })
            ->when($this->selectedSemester, function (Builder $query) {
                $query->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('semester', $this->selectedSemester));
            })
            ->when($this->selectedPaperType, function (Builder $query) {
                $query->whereHas('paper', fn (Builder $paperQuery) => $paperQuery
                    ->where('paper_type', $this->selectedPaperType));
            })
            ->when($this->selectedPaper, fn (Builder $query) => $query
                ->where('paper_id', $this->selectedPaper))
            ->when($this->selectedTeacher, fn (Builder $query) => $query
                ->where('teacher_id', $this->selectedTeacher))
            ->orderByDesc('id');
    }

    public function render()
    {
        $marksData = $this->marksQuery()->paginate(15);
        $papers = Paper::query()
            ->whereHas('theoryMarks')
            ->when($this->selectedDepartment, fn (Builder $query) => $query
                ->where('dept_id', $this->selectedDepartment))
            ->when($this->selectedCourse, fn (Builder $query) => $query
                ->where('course_id', $this->selectedCourse))
            ->when($this->selectedSemester, fn (Builder $query) => $query
                ->where('semester', $this->selectedSemester))
            ->when($this->selectedPaperType, fn (Builder $query) => $query
                ->where('paper_type', $this->selectedPaperType))
            ->when($this->selectedTeacher, fn (Builder $query) => $query
                ->whereHas('theoryMarks', fn (Builder $marksQuery) => $marksQuery
                    ->where('teacher_id', $this->selectedTeacher)))
            ->where(function (Builder $query) {
                $this->applyEligiblePaperCriteria($query);
            })
            ->orderBy('name')
            ->get();

        $teachers = Teacher::query()
            ->whereIn('id', StudentTheoryMark::query()->distinct()->pluck('teacher_id'))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.theory-marks', compact('marksData', 'papers', 'teachers'));
    }

    private function applyEligiblePaperCriteria(Builder $query): void
    {
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
    }
}
