<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Paper;
use App\Models\StudentAcademic;
use App\Models\StudentSubjectRegistrationForm;

class StudentSubjectRegistration extends Component
{
    public $academic_year;
    public $selectedPapers = [];
    public $nextSemester;

    public function mount()
    {
        $year = date('Y');
        $next = substr($year + 1, -2);

        $this->academic_year = $year . '-' . $next;

        $student = Auth::guard('student')->user();

        $academic = StudentAcademic::where(
            'student_user_id',
            $student->id
        )->first();

        if ($academic) {
            $this->nextSemester = $academic->current_semester + 1;
        }
    }

    public function save()
{
    $student = Auth::guard('student')->user();

    $academic = StudentAcademic::where(
        'student_user_id',
        $student->id
    )->first();

    // SAME DEPARTMENT PAPERS
    $papers = Paper::where('dept_id', $academic->department_id)
        ->where('course_id', $academic->course_id)
        ->where('semester', $this->nextSemester)
        ->where('status', 'Active')
        ->where('paper_type', '!=', 'DSC')
        ->where('paper_type', '!=', 'GE')
        ->get()
        ->groupBy('paper_type');

    // VALIDATE OTHER TYPES
    foreach ($papers as $type => $items) {

        if (!isset($this->selectedPapers[$type])) {

            $this->addError(
                'selectedPapers.' . $type,
                "Please select one {$type} paper."
            );

            return;
        }
    }

    // VALIDATE GE
    if (!isset($this->selectedPapers['GE'])) {

        $this->addError(
            'selectedPapers.GE',
            'Please select one GE paper.'
        );

        return;
    }

    // AUTO SAVE DSC PAPERS
    $dscPapers = Paper::where('dept_id', $academic->department_id)
        ->where('course_id', $academic->course_id)
        ->where('semester', $this->nextSemester)
        ->where('paper_type', 'DSC')
        ->where('status', 'Active')
        ->get();

    foreach ($dscPapers as $paper) {

        StudentSubjectRegistrationForm::updateOrCreate(
            [
                'student_user_id' => $student->id,
                'paper_master_id' => $paper->id,
                'semester' => $this->nextSemester,
                'academic_year' => $this->academic_year,
            ]
        );
    }

    // SAVE OTHER TYPES
    foreach ($this->selectedPapers as $type => $paperId) {

        StudentSubjectRegistrationForm::updateOrCreate(
            [
                'student_user_id' => $student->id,
                'paper_master_id' => $paperId,
                'semester' => $this->nextSemester,
                'academic_year' => $this->academic_year,
            ]
        );
    }

    session()->flash(
        'success',
        'Subject registration completed successfully.'
    );
}

    public function render()
{
    $student = Auth::guard('student')->user();

    $academic = StudentAcademic::where(
        'student_user_id',
        $student->id
    )->first();

    $sameDeptPapers = collect();
    $gePapers = collect();

    if ($academic) {

        // SAME DEPARTMENT PAPERS
        $corePapers = Paper::where('dept_id', $academic->department_id)
            ->where('course_id', $academic->course_id)
            ->where('semester', $this->nextSemester)
            ->where('status', 'Active')
            ->where('paper_type', '=', 'DSC')
            ->orderBy('paper_type')
            ->get();

        $secPapers = Paper::where('semester', $this->nextSemester)
            ->where('status', 'Active')
            ->where('paper_type', '=', 'SEC')
            ->orderBy('paper_type')
            ->get();
        $vacPapers = Paper::where('semester', $this->nextSemester)
            ->where('status', 'Active')
            ->where('paper_type', '=', 'VAC')
            ->orderBy('paper_type')
            ->get();

        $dsePapers = Paper::where('dept_id', $academic->department_id)
            ->where('course_id', $academic->course_id)
            ->where('semester', $this->nextSemester)
            ->where('status', 'Active')
            ->where('paper_type', '=', 'DSE')
            ->orderBy('paper_type')
            ->get();
        // GE PAPERS FROM OTHER DEPARTMENT
        $gePapers = Paper::where('dept_id', '!=', $academic->department_id)
            ->where('semester', $this->nextSemester)
            ->where('paper_type', 'GE')
            ->where('status', 'Active')
            ->get();
        // AEC PAPERS FROM OTHER DEPARTMENT
        $aecPapers = Paper::where('semester', $this->nextSemester)
            ->where('paper_type', 'AEC')
            ->where('status', 'Active')
            ->get();
    }

    $registered = StudentSubjectRegistrationForm::with('paper')
        ->where('student_user_id', $student->id)
        ->latest()
        ->get();

    return view('livewire.student-subject-registration', [
        'corePapers' => $corePapers,
        'secPapers' => $secPapers,
        'vacPapers'=> $vacPapers,
        'dsePapers'=> $dsePapers,
        'gePapers' => $gePapers,
        'aecPapers'=>$aecPapers,
        'registered' => $registered,
        'academic' => $academic,
    ]);
}
}