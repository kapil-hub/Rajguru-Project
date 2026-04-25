<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Models\StudentPracticalMark;
use App\Models\TeacherClassAssignment;
use App\Models\Paper;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewPracticalMarks extends Component
{
    public $subjects = [];
    public $selectedPaper = null;
    public $students = [];
    public $showStudents = false;
    public $selectedSubject = null;
    public $assignment = [];
   

    public function mount()
    {
        // Get paper IDs where this teacher has entered marks
        $paperIds = StudentPracticalMark::where('teacher_id', Auth::id())
            ->distinct()
            ->pluck('paper_id')
            ->toArray();

        $this->subjects = Paper::whereIn('id', $paperIds)->get();
        
    }

    public function viewStudents($id)
    {
        $this->selectedSubject = $this->subjects->firstWhere('id', $id);
        $this->selectedPaper = $id;
        $this->students = StudentPracticalMark::with(['student.academic'])
            ->where('paper_id', $id)
            ->get();
        $this->assignment = TeacherClassAssignment::where("teacher_id", Auth::id())->where('paper_master_id', $id)->first();
        $this->showStudents = true;
    }

     public function isOnlyTotal()
{
    if (!$this->selectedSubject) return false;

    $p = $this->selectedSubject;

    return (
        (
            in_array($p->paper_type, ['SEC', 'VAC']) &&
            $p->number_of_lectures == 0 &&
            $p->number_of_tutorials == 0 &&
            $p->number_of_practicals == 2
        )
        ||
        (
            in_array($p->paper_type, ['SEC', 'VAC', 'AEC']) &&
            $p->number_of_lectures == 1 &&
            $p->number_of_tutorials == 0 &&
            $p->number_of_practicals == 1
        )
    );
}


    public function downloadPdf()
    {
        if (!$this->selectedSubject) {
            return;
        }

        $pdf = Pdf::loadView('pdf.practical-marks', [
            'subject' => $this->selectedSubject,
            'students' => $this->students,
            'assignment' => $this->assignment,
            'showTotalOnly'=>$this->isOnlyTotal(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            "practical-marks.pdf"
        );
    }

    public function render()
    {
        return view('livewire.teacher.view-practical-marks');
    }
}
