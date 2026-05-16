<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StudentPracticalMark;
use App\Models\Courses;
use App\Models\Paper;
use App\Models\Departments;
use App\Exports\PracticalMarksExport;
use Maatwebsite\Excel\Facades\Excel;

class PracticalMarks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selectedDepartment = '';

    public $departments = [];
    public $semesters = [];
    public $courseIds = [];
    public $paperMamsterID = [];


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
        ]);
        $this->paperMamsterID = Paper::where('dept_id',$this->selectedDepartment)->where("number_of_practicals",'!=',0)->pluck("id")->toArray();
        $this->courseIds = Courses::where('dept_id', $this->selectedDepartment)
            ->pluck('id')
            ->toArray();
       
        $this->resetPage();
    }
    public function exportExcel()
{
    $this->validate([
        'selectedDepartment' => 'required',
    ]);

    if (empty($this->courseIds)) {

        $this->courseIds = Courses::where('dept_id', $this->selectedDepartment)
            ->pluck('id')
            ->toArray();
    }
    if (empty($this->paperMamsterID)) {

        $this->paperMamsterID = Paper::where('dept_id',$this->selectedDepartment)->pluck("id")->toArray();
    }
    $departmentName = $this->departments->where('id',$this->selectedDepartment)->first();
    $filename = "FOR SAMARTH__".$departmentName->name;
    return Excel::download(
        new PracticalMarksExport($this->paperMamsterID),
        $filename.'.xlsx'
    );
}

    public function render()
    {
        $marksData = StudentPracticalMark::query();

        if (!empty($this->paperMamsterID)) {

            $marksData = $marksData
                ->with([
                    'student',
                    'student.academic',
                    'paper',
                    'paper.course',
                    'course'
                ])
                ->whereIn('paper_id', $this->paperMamsterID)
                //->where('semester_id', $this->selectedSemester)
                ->orderBy('id', 'DESC');

        } else {

            $marksData = $marksData->whereRaw('1 = 0');
        }

        return view('livewire.admin.practical-marks', [
            'marksData' => $marksData->paginate(10)
        ]);
    }
}