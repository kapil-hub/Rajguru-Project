<?php

namespace App\Livewire\Teacher;

use App\Models\Paper;
use App\Models\Student;
use App\Models\StudentTheoryMark;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TheoryMarks extends Component
{
    public $papers = [];
    public $selectedPaper = '';
    public $students = [];
    public $marks = [];
    public $maxMarks = 0;
    public $showStudents = false;

    public function mount(): void
    {
        $this->papers = $this->eligiblePapers()->orderBy('name')->get();
    }

    public function updatedSelectedPaper(): void
    {
        $this->students = [];
        $this->marks = [];
        $this->showStudents = false;
    }

    public function loadStudents(): void
    {
        $paper = $this->eligiblePapers()->findOrFail($this->selectedPaper);
        $this->maxMarks = lectureMarksBreakup((int) $paper->number_of_lectures)['theory'];

        $this->students = $this->studentsForPaper($paper->id);

        $existingMarks = StudentTheoryMark::where('paper_id', $paper->id)
            ->whereIn('student_id', $this->students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        foreach ($this->students as $student) {
            $this->marks[$student->id] = $existingMarks->get($student->id)?->theory_marks;
        }

        $this->showStudents = true;
    }

    public function downloadBlankPdf()
    {
        $paper = $this->eligiblePapers()->findOrFail($this->selectedPaper);
        $students = $this->studentsForPaper($paper->id);
        $maxMarks = lectureMarksBreakup((int) $paper->number_of_lectures)['theory'];

        $pdf = Pdf::loadView('pdf.blank-theory-marks', compact('paper', 'students', 'maxMarks'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'blank-theory-marks-' . $paper->code . '.pdf'
        );
    }

    public function saveMarks(): void
    {
        $paper = $this->eligiblePapers()->findOrFail($this->selectedPaper);
        $maxMarks = lectureMarksBreakup((int) $paper->number_of_lectures)['theory'];

        $students = Student::whereHas('papers', function (Builder $query) use ($paper) {
            $query->where('paper_master_id', $paper->id);
        })->get();

        $rules = [];
        foreach ($students as $student) {
            $rules["marks.{$student->id}"] = ['nullable', 'numeric', 'min:0', "max:{$maxMarks}"];
        }
        $this->validate($rules);

        foreach ($students as $student) {
            StudentTheoryMark::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'paper_id' => $paper->id,
                ],
                [
                    'teacher_id' => Auth::guard('teacher')->id(),
                    'theory_marks' => $this->marks[$student->id] ?? null,
                ]
            );
        }

        session()->flash('success', 'Theory marks saved successfully.');
    }

    private function eligiblePapers(): Builder
    {
        return Paper::query()->eligibleForTheoryMarksByCoordinator(
            (int) Auth::guard('teacher')->id()
        );
    }

    private function studentsForPaper(int $paperId)
    {
        return Student::with(['academic.course', 'papers'])
            ->whereHas('papers', function (Builder $query) use ($paperId) {
                $query->where('paper_master_id', $paperId);
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.teacher.theory-marks');
    }
}
