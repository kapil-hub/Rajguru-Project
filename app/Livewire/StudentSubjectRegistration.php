<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Paper;
use App\Models\StudentAcademic;
use App\Models\StudentSubjectRegistrationForm;

class StudentSubjectRegistration extends Component
{
    public $academic_year;
    public $isEdit = false;
    public $selectedPapers = [
        'DSE' => [],
        'GE' => [],
        'SEC' => null,
        'VAC' => null,
        'AEC' => null,
    ];

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
            $existing = StudentSubjectRegistrationForm::with('paper')
            ->where('student_user_id', $student->id)
            ->where('semester', $this->nextSemester)
            ->get();

            if ($existing->count()) {

                $this->isEdit = true;

                foreach ($existing as $row) {

                    $paperType = $row->paper->paper_type ?? null;

                    if (!$paperType) {
                        continue;
                    }

                    // DSC auto ignore
                    if ($paperType === 'DSC') {
                        continue;
                    }

                    // multi select
                    if (in_array($paperType, ['DSE', 'GE'])) {

                        $this->selectedPapers[$paperType][] = $row->paper_master_id;

                    } else {

                        $this->selectedPapers[$paperType] = $row->paper_master_id;
                    }
                }
            }
        }
    }

   public function rules()
{
    /*
    |--------------------------------------------------------------------------
    | SEMESTER 3
    |--------------------------------------------------------------------------
    */

    if ($this->nextSemester == 3) {

        return [

            'selectedPapers.VAC' => 'required',

            'selectedPapers.SEC' => 'required',

            'selectedPapers.AEC' => 'required',

            'selectedPapers.DSE' => 'nullable|required_without:selectedPapers.GE',
            'selectedPapers.GE'  => 'nullable|required_without:selectedPapers.DSE',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SEMESTER 5
    |--------------------------------------------------------------------------
    */

    if ($this->nextSemester == 5) {

        return [

            'selectedPapers.DSE' => 'required',

            'selectedPapers.GE' => 'required',

            'selectedPapers.SEC' => 'required',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SEMESTER 7
    |--------------------------------------------------------------------------
    */

    if ($this->nextSemester == 7) {

        return [

            'selectedPapers.DSE' => 'required|array|min:1|max:3',

            'selectedPapers.GE' => 'nullable|array|max:2',

        ];
    }

    return [];
}
public function validateSemesterRules()
{
    /*
    |--------------------------------------------------------------------------
    | SEMESTER 7 CUSTOM VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($this->nextSemester == 7) {

        $dseCount = count(array_filter($this->selectedPapers['DSE'] ?? []));
        $geCount  = count(array_filter($this->selectedPapers['GE'] ?? []));

        $valid = false;

        /*
        |--------------------------------------------------------------------------
        | VALID COMBINATIONS
        |--------------------------------------------------------------------------
        |
        | 3 DSE
        | 2 DSE + 1 GE
        | 1 DSE + 2 GE
        |--------------------------------------------------------------------------
        */

        if ($dseCount == 3 && $geCount == 0) {
            $valid = true;
        }

        if ($dseCount == 2 && $geCount == 1) {
            $valid = true;
        }

        if ($dseCount == 1 && $geCount == 2) {
            $valid = true;
        }

        if (!$valid) {

            $this->addError(
                'selectedPapers.DSE',
                'Allowed combinations are:
                [3 DSE]
                [2 DSE + 1 GE]
                [1 DSE + 2 GE]'
            );

            return false;
        }
    }

    return true;
}

    public function save()
    {
        $student = Auth::guard('student')->user();

        $academic = StudentAcademic::where(
            'student_user_id',
            $student->id
        )->first();

        if (!$academic) {

            session()->flash(
                'error',
                'Academic record not found.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ONLY FOR 3rd / 5th / 7th
        |--------------------------------------------------------------------------
        */

        if (!in_array($this->nextSemester, [3, 5, 7])) {

            session()->flash(
                'error',
                'Registration allowed only for 3rd, 5th and 7th semester.'
            );

            return;
        }

        $this->validate();

        if (!$this->validateSemesterRules()) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | PREVENT DUPLICATE REGISTRATION
        |--------------------------------------------------------------------------
        */

        StudentSubjectRegistrationForm::where(
            'student_user_id',
            $student->id
        )
        ->where('semester', $this->nextSemester)
        ->delete();

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | AUTO ADD DSC PAPERS
            |--------------------------------------------------------------------------
            */

            $dscPapers = Paper::where(
                'dept_id',
                $academic->department_id
            )
                ->where('course_id', $academic->course_id)
                ->where('semester', $this->nextSemester)
                ->where('paper_type', 'DSC')
                ->where('status', 'Active')
                ->get();

            foreach ($dscPapers as $paper) {

                StudentSubjectRegistrationForm::create([
                    'student_user_id' => $student->id,
                    'paper_master_id' => $paper->id,
                    'semester' => $this->nextSemester,
                    'academic_year' => $this->academic_year,
                    'is_approved' => 0,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE ALL SELECTED PAPERS
            |--------------------------------------------------------------------------
            */

            foreach ($this->selectedPapers as $type => $value) {

                if (is_array($value)) {

                    foreach ($value as $paperId) {

                        if (!$paperId) {
                            continue;
                        }

                        StudentSubjectRegistrationForm::create([
                            'student_user_id' => $student->id,
                            'paper_master_id' => $paperId,
                            'semester' => $this->nextSemester,
                            'academic_year' => $this->academic_year,
                            'is_approved' => 0,
                        ]);
                    }
                } else {

                    if (!$value) {
                        continue;
                    }

                    StudentSubjectRegistrationForm::create([
                        'student_user_id' => $student->id,
                        'paper_master_id' => $value,
                        'semester' => $this->nextSemester,
                        'academic_year' => $this->academic_year,
                        'is_approved' => 0,
                    ]);
                }
            }

            DB::commit();

            session()->flash(
                'success',
                'Subject registration submitted successfully.'
            );

            session()->flash(
                'success',
                $this->isEdit
                    ? 'Subject registration updated successfully.'
                    : 'Subject registration submitted successfully.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function render()
    {
        $student = Auth::guard('student')->user();

        $academic = StudentAcademic::with(['department','course'])->where(
            'student_user_id',
            $student->id
        )->first();

        $corePapers = collect();
        $dsePapers = collect();
        $gePapers = collect();
        $secPapers = collect();
        $vacPapers = collect();
        $aecPapers = collect();

        if ($academic) {

            /*
            |--------------------------------------------------------------------------
            | DSC
            |--------------------------------------------------------------------------
            */

            $corePapers = Paper::where(
                'dept_id',
                $academic->department_id
            )
                ->where('course_id', $academic->course_id)
                ->where('semester', $this->nextSemester)
                ->where('paper_type', 'DSC')
                ->where('status', 'Active')
                ->get()
                ->filter(function ($paper) {

                    if (is_null($paper->capping)) {
                        return true;
                    }

                     return $paper->registrations()->count() < $paper->capping;
                });

            /*
            |--------------------------------------------------------------------------
            | DSE
            |--------------------------------------------------------------------------
            */

            $dsePapers = Paper::where(
                'dept_id',
                $academic->department_id
            )
                ->where('course_id', $academic->course_id)
                ->where('semester', $this->nextSemester)
                ->where('paper_type', 'DSE')
                ->where('status', 'Active')
                ->get()
                ->filter(function ($paper) {

                    if (is_null($paper->capping)) {
                        return true;
                    }

                     return $paper->registrations()->count() < $paper->capping;
                });
            
            /*
            |--------------------------------------------------------------------------
            | GE
            |--------------------------------------------------------------------------
            */

            $gePapers = Paper::where(
                'dept_id',
                '!=',
                $academic->department_id
            )
                ->where('semester', $this->nextSemester)
                ->where('paper_type', 'GE')
                ->where('status', 'Active')
                ->get()
                ->filter(function ($paper) {

                    if (is_null($paper->capping)) {
                        return true;
                    }

                     return $paper->registrations()->count() < $paper->capping;
                });

            /*
            |--------------------------------------------------------------------------
            | SEC
            |--------------------------------------------------------------------------
            */

            $secPapers = Paper::where(
                'semester',
                $this->nextSemester
            )
                ->where('paper_type', 'SEC')
                ->where('status', 'Active')
                ->get()
                ->filter(function ($paper) {

                    if (is_null($paper->capping)) {
                        return true;
                    }

                    return $paper->registrations()->count() < $paper->capping;
                });

            /*
            |--------------------------------------------------------------------------
            | VAC
            |--------------------------------------------------------------------------
            */

            $vacPapers = Paper::where(
                'semester',
                $this->nextSemester
            )
                ->where('paper_type', 'VAC')
                ->where('status', 'Active')
                ->get()
                ->filter(function ($paper) {

                    if (is_null($paper->capping)) {
                        return true;
                    }

                    return $paper->registrations()->count() < $paper->capping;
                });

            /*
            |--------------------------------------------------------------------------
            | AEC
            |--------------------------------------------------------------------------
            */

            $aecPapers = Paper::where(
                'semester',
                $this->nextSemester
            )
                ->where('paper_type', 'AEC')
                ->where('status', 'Active')
                ->get()
                ->filter(function ($paper) {

                    if (is_null($paper->capping)) {
                        return true;
                    }

                     return $paper->registrations()->count() < $paper->capping;
                });
        }

        $registered = StudentSubjectRegistrationForm::with('paper')
            ->where('student_user_id', $student->id)
            ->latest()
            ->get()
                ->filter(function ($paper) {

                    if (is_null($paper->capping)) {
                        return true;
                    }

                    return $paper->registrations()->count() < $paper->capping;
                });

        return view(
            'livewire.student-subject-registration',
            [
                'corePapers' => $corePapers,
                'dsePapers' => $dsePapers,
                'gePapers' => $gePapers,
                'secPapers' => $secPapers,
                'vacPapers' => $vacPapers,
                'aecPapers' => $aecPapers,
                'registered' => $registered,
                'academic' => $academic,
            ]
        );
    }
}