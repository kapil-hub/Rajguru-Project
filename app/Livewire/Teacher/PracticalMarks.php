<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Models\Student;
use App\Models\Paper;
use App\Models\PaperTimetable;
use App\Models\StudentPracticalMark;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PracticalMarks extends Component
{
    public $papers = [];
    public $selectedPaper = null;
    public $students = [];
    public $marks = [];
    public $practicleBreakup = [];
    public $showStudents = false;
    public $paper =[];
    public bool $hasMarkErrors = false;

    public function mount()
    {
        // Get assigned practical paper IDs

        
        $paperIds = PaperTimetable::where('is_practical', true)
            ->where('teacher_id', Auth::id())
            ->pluck('paper_id')
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

        $this->paper = $this->assignedPracticalPaper();
        $this->students = $this->studentsForPaper($this->paper->id);

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
                'total' => $existing->total_marks ?? 0,
            ];
        }
        
        $credit = $this->paper->number_of_practicals ?? 0;

        if($credit == 0){
            session()->flash('error', 'Selected Paper does not has practical');
            return;
        }
      
        $this->practicleBreakup = practicalMarksBreakup($credit);

        $this->showStudents = true;
    }

    public function downloadBlankPdf()
    {
        $paper = $this->assignedPracticalPaper();
        $students = $this->studentsForPaper($paper->id);
        $breakup = practicalMarksBreakup((int) $paper->number_of_practicals);
        $onlyTotal = $this->isOnlyTotalForPaper($paper);

        $pdf = Pdf::loadView('pdf.blank-practical-marks', compact(
            'paper',
            'students',
            'breakup',
            'onlyTotal'
        ))->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'blank-practical-marks-' . $paper->code . '.pdf'
        );
    }

    private function assignedPracticalPaper(): Paper
    {
        return Paper::query()
            ->whereKey($this->selectedPaper)
            ->where('number_of_practicals', '>', 0)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('paper_timetables')
                    ->whereColumn('paper_timetables.paper_id', 'paper_master.id')
                    ->where('paper_timetables.teacher_id', Auth::guard('teacher')->id())
                    ->where('paper_timetables.is_practical', true);
            })
            ->firstOrFail();
    }

    private function studentsForPaper(int $paperId)
    {
        return Student::with(['academic.course', 'papers'])
            ->whereHas('papers', function ($query) use ($paperId) {
                $query->where('paper_master_id', $paperId);
            })
            ->leftJoin('student_academic', 'student_academic.student_user_id', '=', 'student_users.id')
            ->orderBy('student_academic.roll_number')
            ->select('student_users.*')
            ->get();
    }

    private function isOnlyTotalForPaper(Paper $paper): bool
    {
        return (
            (
                in_array($paper->paper_type, ['SEC', 'VAC']) &&
                $paper->number_of_lectures == 0 &&
                $paper->number_of_tutorials == 0 &&
                $paper->number_of_practicals == 2
            )
            ||
            (
                in_array($paper->paper_type, ['SEC', 'VAC', 'AEC']) &&
                $paper->number_of_lectures == 1 &&
                $paper->number_of_tutorials == 0 &&
                $paper->number_of_practicals == 1
            )
        );
    }


    public function isOnlyTotal()
{
    if (!$this->paper) return false;

    $p = $this->paper;

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
    public function updated($property, $value)
{
    // Only handle marks updates
    if (!str_starts_with($property, 'marks.')) {
        return;
    }

    // marks.5.ca → [marks, 5, ca]
    $parts = explode('.', $property);

    if (count($parts) !== 3) {
        return;
    }

    [, $studentId, $field] = $parts;

    $maxLimits = [
        'ca'    => $this->practicleBreakup['ca'] ?? 0,
        'esp'   => $this->practicleBreakup['written_exam'] ?? 0,
        'viva'  => $this->practicleBreakup['viva_voce'] ?? 0,
        'total' => $this->practicleBreakup['total'] ?? 0,
    ];

    // Prevent negative values
    if ($value < 0) {
        $this->marks[$studentId][$field] = 0;
        $this->hasMarkErrors = true;
        return;
    }

    // Enforce max limit
    if (isset($maxLimits[$field]) && $value > $maxLimits[$field]) {
        $this->marks[$studentId][$field] = $maxLimits[$field];
        $this->hasMarkErrors = true;

        session()->flash(
            'error',
            ucfirst($field) . " cannot be greater than " . $maxLimits[$field]
        );
    }
}

    public function saveMarks()
    {
        if ($this->hasMarkErrors) {
            session()->flash('error', 'Please review corrected marks before saving.');
            $this->hasMarkErrors = false;
            return;
        }

        foreach ($this->students as $student) {

            $ca   = $this->marks[$student->id]['ca'] ?? 0;
            $esp  = $this->marks[$student->id]['esp'] ?? 0;
            $viva = $this->marks[$student->id]['viva'] ?? 0;
            if($this->isOnlyTotal()){
                $total =  $this->marks[$student->id]['total'];
            }else{
                $total =  $ca + $esp + $viva;
            }
           

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
                    'total_marks'           => $total ?? ($ca + $esp + $viva),
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
