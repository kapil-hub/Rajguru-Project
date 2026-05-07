<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\StudentPaper;
use App\Models\StudentLog;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class StudentLogs extends Component
{
     use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $filterStudent = '';
    public $filterPaper = '';
    public $students = [];
    public $papers = [];


    public $student_user_id;
    public $paper_master_id;
    public $log_count;
    public $remark;

    public $log_id;
    public $isEdit = false;

    public function mount()
    {
        $this->students = Student::with('academic')->get();
   
    }
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatedStudentUserId($value)
    {
        $this->papers = StudentPaper::with('paper')
            ->where('student_user_id', $value)
            ->get();

        $this->paper_master_id = null;
    }

    public function resetForm()
    {
        $this->reset([
            'student_user_id',
            'paper_master_id',
            'log_count',
            'remark',
            'log_id',
            'isEdit',
            'papers'
        ]);
    }

    public function store()
    {
        $this->validate([
            'student_user_id' => 'required',
            'paper_master_id' => 'required',
            'log_count' => 'required|integer',
        ]);
         $exists = StudentLog::where('student_user_id', $this->student_user_id)
            ->where('paper_master_id', $this->paper_master_id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'This student already has a log for this paper.');
            return;
        }

        StudentLog::create([
            'student_user_id' => $this->student_user_id,
            'paper_master_id' => $this->paper_master_id,
            'log_count' => $this->log_count,
            'remark' => $this->remark,
            'created_by_auth_type' => 'admin',
            'created_by_id' => Auth::id(),
        ]);

        session()->flash('message', 'Created successfully');

        $this->resetForm();
      
        $this->dispatch('resetStudentSelect');
    }

    public function edit($id)
    {
        $log = StudentLog::findOrFail($id);

        $this->student_user_id = $log->student_user_id;

        // load papers for selected student
        $this->updatedStudentUserId($this->student_user_id);

        $this->paper_master_id = $log->paper_master_id;
        $this->log_count = $log->log_count;
        $this->remark = $log->remark;

        $this->log_id = $id;
        $this->isEdit = true;
        $this->dispatch('setStudentSelect', studentId: $this->student_user_id);
    }

    public function update()
    {
        $this->validate([
            'student_user_id' => 'required',
            'paper_master_id' => 'required',
            'log_count' => 'required|integer',
        ]);

        StudentLog::find($this->log_id)->update([
            'student_user_id' => $this->student_user_id,
            'paper_master_id' => $this->paper_master_id,
            'log_count' => $this->log_count,
            'remark' => $this->remark,
        ]);

        session()->flash('message', 'Updated successfully');

        $this->resetForm();

        $this->dispatch('resetStudentSelect');
    }

    public function delete($id)
    {
        StudentLog::find($id)->delete();

        session()->flash('message', 'Deleted successfully');

     
    }

    public function render()
{
    $logs = StudentLog::with(['student', 'paper'])
        ->when($this->search, function ($query) {
            $query->whereHas('student', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })->orWhereHas('paper', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->filterStudent, fn($q) =>
                $q->where('student_user_id', $this->filterStudent)
            )
            ->when($this->filterPaper, fn($q) =>
                $q->where('paper_master_id', $this->filterPaper)
            )
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate(10);
    return view('livewire.student-logs', [
        'logs' => $logs
    ]);
}
}