<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Models\StudentPracticalMark;
use App\Models\Paper;
use Illuminate\Support\Facades\Auth;

class ViewPracticalMarks extends Component
{
    public $subjects = [];
    public $selectedPaper = null;
    public $students = [];
    public $showStudents = false;
    public $selectedSubject = null;

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

        $this->showStudents = true;
    }

    public function render()
    {
        return view('livewire.teacher.view-practical-marks');
    }
}
