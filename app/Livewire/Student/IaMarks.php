<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\IaMark;
use App\Models\Student;
use App\Models\Paper;
use Illuminate\Support\Facades\Auth;

class IaMarks extends Component
{
    public $student;

    public $papers = [];

    public $openPaper = null;

    public $marks = [];

    public function mount()
{
    $this->student = auth('student')->user();
    
    $this->papers = $this->student->papers()->get();

    foreach ($this->papers as $studentPaper) {

        $paperId = $studentPaper->paper_master_id;

        $ia = IaMark::where('student_id', $this->student->id)
            ->where('paper_master_id', $paperId)
            ->first();
        
        $this->marks[$paperId] = [
            'id' => $ia?->id,
            'tute_ca' => (float) ($ia?->tute_ca ?? 0),
            'tute_attendance' => (float) ($ia?->tute_attendance ?? 0),
            'class_test' => (float) ($ia?->class_test ?? 0),
            'total' => (float) ($ia?->total ?? 0),
            'assignment' => (float) ($ia?->assignment ?? 0),
            'attendance' => (float) ($ia?->attendance ?? 0),
        ];
 
    }
}


public function isOnlyTotal($paperId)
{
    if (!$paperId) return false;

    $p = Paper::where('id',$paperId)->get()->first();

    return (
        (
            ($p->paper_type == 'SEC' || $p->paper_type == 'VAC' || $p->paper_type == 'AEC')
            &&
            ($p->number_of_lectures == 1 && $p->number_of_tutorials == 0 && $p->number_of_practicals == 1)
        )
        || (
            $p->paper_type == 'AEC'
            && $p->number_of_lectures == 2
            && $p->number_of_tutorials == 0
            && $p->number_of_practicals == 0
        )
    );
    }
    public function togglePaper($paperId)
    {
        $this->openPaper = $this->openPaper === $paperId ? null : $paperId;
    }

    public function save($paperId)
    {
        $data = $this->marks[$paperId];

        $total = (
            (float) $data['class_test'] +
            (float) $data['assignment'] +
            (float) $data['attendance']
        );

        $totalTute = (
            (float) $data['tute_ca'] +
            (float) $data['tute_attendance']
        );

        $grandTotal = $total + $totalTute;

        IaMark::where('id', $data['id'])->update([
            'tute_ca' => $data['tute_ca'],
            'tute_attendance' => $data['tute_attendance'],
            'class_test' => $data['class_test'],
            'assignment' => $data['assignment'],
            'attendance' => $data['attendance'],
            'total' => $total,
            'total_tute_marks' => $totalTute,
            'grand_total' => $grandTotal,
        ]);

        session()->flash('message', 'IA Marks updated successfully.');
    }

    public function render()
    {
        return view('livewire.student.ia-marks');
    }
}