<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class StudentattendanceBreakup extends Component
{
    public $student;
    public $papers = [];
    public $show = false;
    public $loading = true;

    protected $listeners = ['loadBreakup'];

    public function mount($student)
    {
        $this->student = $student;
    }

    public function loadBreakup($id = null)
    {
        if (!$id || $id != $this->student->student_id) {
            return;
        }

        // Only toggle visibility
        $this->show = ! $this->show;
        
        if ($this->show) {
            $this->loadPapers();
        } else {
            $this->papers = [];
        }
    }



    public function loadPapers()
    {
        
        $this->papers = DB::table('student_attendances as sa')
            ->join('paper_master as pm', 'pm.id', '=', 'sa.paper_master_id')
            ->where('sa.student_id', $this->student->student_id)
            ->select(
                'pm.name as paper_name',
                'sa.lecture_working_days',
                'sa.lecture_present_days',
                'sa.tute_working_days',
                'sa.tute_present_days',
                'sa.practical_working_days',
                'sa.practical_present_days'
            )
            ->get();

    }

    public function render()
    {
        return view('livewire.admin.studentattendance-breakup');
    }
}
