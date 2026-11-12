<?php

namespace App\Livewire\Teacher;

use App\Models\Paper;
use App\Models\StudentTheoryMark;
use App\Exports\TheoryMarksExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ViewTheoryMarks extends Component
{
    public $papers = [];
    public $students = [];
    public $selectedSubject = null;
    public $showStudents = false;

    public function mount(): void
    {
        $teacherId = (int) Auth::guard('teacher')->id();
        $paperIds = StudentTheoryMark::query()
            ->where('teacher_id', $teacherId)
            ->distinct()
            ->pluck('paper_id');

        $this->papers = Paper::query()
            ->eligibleForTheoryMarksByCoordinator($teacherId)
            ->whereIn('id', $paperIds)
            ->orderBy('name')
            ->get();
    }

    public function viewStudents(int $paperId): void
    {
        $this->selectedSubject = $this->paperWithMarks($paperId);
        $this->students = $this->marksForPaper($paperId);
        $this->showStudents = true;
    }

    public function downloadPdf()
    {
        $subject = $this->paperWithMarks((int) $this->selectedSubject?->id);
        $students = $this->marksForPaper($subject->id);
        $maxMarks = lectureMarksBreakup((int) $subject->number_of_lectures)['theory'];

        $pdf = Pdf::loadView('pdf.theory-marks', compact('subject', 'students', 'maxMarks'))
            ->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'theory-marks-' . $subject->code . '.pdf'
        );
    }

    public function downloadExcel()
    {
        $subject = $this->paperWithMarks((int) $this->selectedSubject?->id);

        return Excel::download(
            new TheoryMarksExport($subject->id, (int) Auth::guard('teacher')->id()),
            'theory-marks-' . $subject->code . '.xlsx'
        );
    }

    private function paperWithMarks(int $paperId): Paper
    {
        return Paper::query()
            ->eligibleForTheoryMarksByCoordinator((int) Auth::guard('teacher')->id())
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('student_theory_marks')
                    ->whereColumn('student_theory_marks.paper_id', 'paper_master.id')
                    ->where('student_theory_marks.teacher_id', (int) Auth::guard('teacher')->id());
            })
            ->findOrFail($paperId);
    }

    private function marksForPaper(int $paperId)
    {
        return StudentTheoryMark::query()
            ->with(['student.academic.course'])
            ->where('teacher_id', (int) Auth::guard('teacher')->id())
            ->where('paper_id', $paperId)
            ->leftJoin('student_academic', 'student_academic.student_user_id', '=', 'student_theory_marks.student_id')
            ->orderBy('student_academic.roll_number')
            ->select('student_theory_marks.*')
            ->get();
    }

    public function render()
    {
        return view('livewire.teacher.view-theory-marks');
    }
}
