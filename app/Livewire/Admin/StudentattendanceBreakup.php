<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StudentattendanceBreakup extends Component
{
    public $student;

    public $month;

    public $year;

    public $papers = [];

    public $show = false;

    public $loading = true;

    protected $listeners = ['loadBreakup'];

    public function mount($student, $month, $year)
    {
        $this->month = (int) $month;
        $this->year = (int) $year;
        $this->student = $student;
    }

    public function loadBreakup($id = null)
    {
        if (! $id || $id != $this->student->student_id) {
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
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->groupBy('pm.id', 'pm.name')
            ->select(
                'pm.id as paper_id',
                'pm.name as paper_name',
                DB::raw('SUM(sa.lecture_working_days) as lecture_working_days'),
                DB::raw('SUM(sa.lecture_present_days) as lecture_present_days'),
                DB::raw('SUM(sa.tute_working_days) as tute_working_days'),
                DB::raw('SUM(sa.tute_present_days) as tute_present_days'),
                DB::raw('SUM(sa.practical_working_days) as practical_working_days'),
                DB::raw('SUM(sa.practical_present_days) as practical_present_days')
            )
            ->get();

    }

    public function render()
    {
        return view('livewire.admin.studentattendance-breakup');
    }
}
