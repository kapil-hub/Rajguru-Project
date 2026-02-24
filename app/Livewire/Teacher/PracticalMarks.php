<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Models\Student;
use App\Models\Paper;
use App\Models\TeacherClassAssignment;
use App\Models\StudentPracticalMark;
use Illuminate\Support\Facades\Auth;

class PracticalMarks extends Component
{
    public $papers = [];
    public $selectedPaper = null;
    public $students = [];
    public $marks = [];
    public $practicleBreakup = [];
    public $showStudents = false;

    public function mount()
    {
        // Get assigned practical paper IDs

        
        $paperIds = TeacherClassAssignment::where('is_practical', 1)
            ->where('teacher_id', Auth::id())
            ->pluck('paper_master_id')
            ->toArray();

        $this->papers = Paper::whereIn('id', $paperIds)->get();
        
    }

    // Reset when paper changes
    public function updatedSelectedPaper()
    {
        $this->students = [];
        $this->marks = [];
        $this->showStudents = false;
    }

    public function loadStudents()
    {
        if (!$this->selectedPaper) return;

        $this->students = Student::with(['academic', 'papers'])
            ->whereHas('papers', function ($q) {
                $q->where('paper_master_id', $this->selectedPaper);
            })
            ->get();

        // Preload existing marks
        foreach ($this->students as $student) {

            $existing = StudentPracticalMark::where([
                'student_id' => $student->id,
                'paper_id'   => $this->selectedPaper,
            ])->first();

            $this->marks[$student->id] = [
                'ca'   => $existing->continuous_assessment ?? 0,
                'esp'  => $existing->end_sem_practical ?? 0,
                'viva' => $existing->viva_voce ?? 0,
            ];
        }
        
        $credit  = $this->papers->find($this->selectedPaper)->first()->number_of_practicals ?? 0;
        if($credit == 0){
            session()->flash('error', 'Selected Paper does not has practical');
            return;
        }
        $this->practicleBreakup = practicalMarksBreakup($credit);

        $this->showStudents = true;
    }

    public function updatedMarks($value, $key)
    {
        // $key format: studentId.field (example: 5.ca)
        [$studentId, $field] = explode('.', $key);

        $maxLimits = [
            'ca' => $this->practicleBreakup['ca'],
            'esp' => $this->practicleBreakup['written_exam'],
            'viva' => $this->practicleBreakup['viva_voce'],
        ];

        if (isset($maxLimits[$field]) && $value > $maxLimits[$field]) {

            // Reset to max value
            $this->marks[$studentId][$field] = $maxLimits[$field];

            session()->flash('error', ucfirst($field) . " marks cannot be greater than " . $maxLimits[$field]);
        }
    }

    public function saveMarks()
    {
        foreach ($this->students as $student) {

            $ca   = $this->marks[$student->id]['ca'] ?? 0;
            $esp  = $this->marks[$student->id]['esp'] ?? 0;
            $viva = $this->marks[$student->id]['viva'] ?? 0;

            StudentPracticalMark::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'paper_id'   => $this->selectedPaper,
                ],
                [
                    'teacher_id'            => Auth::id(),
                    'continuous_assessment' => $ca,
                    'end_sem_practical'     => $esp,
                    'viva_voce'             => $viva,
                    'total_marks'           => $ca + $esp + $viva,
                ]
            );
        }

        session()->flash('success', 'Marks saved successfully!');
        $this->showStudents = false;
    }

    public function render()
    {
        return view('livewire.teacher.practical-marks');
    }
}
