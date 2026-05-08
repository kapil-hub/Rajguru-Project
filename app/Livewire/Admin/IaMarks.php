<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\IaMark;
use App\Models\Courses;
use App\Models\Departments;
use App\Exports\IaMarksExport;
use Maatwebsite\Excel\Facades\Excel;

class IaMarks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selectedDepartment = '';
    public $selectedSemester = '';

    public $departments = [];
    public $semesters = [];
    public $courseIds = [];

    public function mount()
    {
        $this->departments = Departments::orderBy('name')->get();

        $this->semesters = [
            "I" => 1,
            "II" => 2,
            "III" => 3,
            "IV" => 4,
            "V" => 5,
            "VI" => 6,
            "VII" => 7,
            "VIII" => 8,
        ];
    }

    public function loadData()
    {
        $this->validate([
            'selectedDepartment' => 'required',
            'selectedSemester' => 'required',
        ]);
        $this->courseIds = Courses::where('dept_id', $this->selectedDepartment)
            ->pluck('id')
            ->toArray();

        $this->resetPage();
    }
    public function exportExcel()
{
    $this->validate([
        'selectedDepartment' => 'required',
        'selectedSemester' => 'required',
    ]);

    if (empty($this->courseIds)) {

        $this->courseIds = Courses::where('dept_id', $this->selectedDepartment)
            ->pluck('id')
            ->toArray();
    }
    $departmentName = $this->departments->where('id',$this->selectedDepartment)->first();
    $filename = "FOR SAMARTH__".$departmentName->name.'__SEM__'.$this->selectedSemester;
    return Excel::download(
        new IaMarksExport($this->courseIds, $this->selectedSemester),
        $filename.'.xlsx'
    );
}

    public function render()
    {
        $marksData = IaMark::query();

        if (!empty($this->courseIds) && !empty($this->selectedSemester)) {

            $marksData = $marksData
                ->with([
                    'student',
                    'student.academic',
                    'paper',
                    'course'
                ])
                ->whereIn('course_id', $this->courseIds)
                ->where('semester_id', $this->selectedSemester)
                ->orderBy('id', 'DESC');

        } else {

            $marksData = $marksData->whereRaw('1 = 0');
        }

        return view('livewire.admin.ia-marks', [
            'marksData' => $marksData->paginate(10)
        ]);
    }
}