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

    protected function ruleSemester(): ?int
    {
        return match ((int) $this->nextSemester) {
            2, 3, 4 => 3,
            5, 6 => 5,
            7, 8 => 7,
            default => null,
        };
    }

   public function rules()
{
    $ruleSemester = $this->ruleSemester();

    if ((int) $this->nextSemester === 2) {
        return [
            'selectedPapers.GE' => 'required',
            'selectedPapers.SEC' => 'required',
            'selectedPapers.VAC' => 'required',
            'selectedPapers.AEC' => 'required',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SEMESTER 3
    |--------------------------------------------------------------------------
    */

    if ($ruleSemester == 3) {

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

    if ($ruleSemester == 5) {

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

    if ($ruleSemester == 7) {

        return [

            'selectedPapers.DSE' => 'required|array|min:1|max:3',
            'selectedPapers.DSE.*' => 'required|integer|distinct',

            'selectedPapers.GE' => 'nullable|array|max:2',
            'selectedPapers.GE.*' => 'required|integer|distinct',

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

    if ($this->ruleSemester() == 7) {

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
        | ONLY FOR 2nd THROUGH 8th
        |--------------------------------------------------------------------------
        */

        if (!in_array((int) $this->nextSemester, [2, 3, 4, 5, 6, 7, 8], true)) {

            session()->flash(
                'error',
                'Registration allowed only for 2nd through 8th semester.'
            );

            return;
        }

        if ((int) $this->nextSemester === 2) {
            $dscCount = Paper::where('dept_id', $academic->department_id)
                ->where('course_id', $academic->course_id)
                ->where('semester', $this->nextSemester)
                ->where('paper_type', 'DSC')
                ->where('status', 'Active')
                ->count();

            if ($dscCount !== 3) {
                session()->flash('error', 'Exactly three active DSC subjects must be available for your department and course.');
                return;
            }

            foreach (['GE' => 'GE', 'SEC' => 'SEC', 'VAC' => 'VAC', 'AEC' => 'AEC'] as $selection => $paperType) {
                $paperQuery = Paper::where('id', $this->selectedPapers[$selection])
                    ->where('semester', $this->nextSemester)
                    ->where('paper_type', $paperType)
                    ->where('status', 'Active');

                if ($selection === 'GE') {
                    $paperQuery->where('dept_id', '!=', $academic->department_id);
                }

                if (!$paperQuery->exists()) {
                    $this->addError(
                        "selectedPapers.$selection",
                        $selection === 'GE'
                            ? 'Select an active GE subject from another department.'
                            : "Select an active $paperType subject."
                    );
                    return;
                }
            }
        }

        $this->validate();

        if ($this->ruleSemester() === 7) {
            $dseIds = array_filter($this->selectedPapers['DSE'] ?? []);
            $geIds = array_filter($this->selectedPapers['GE'] ?? []);

            $validDseCount = Paper::whereIn('id', $dseIds)
                ->where('dept_id', $academic->department_id)
                ->where('course_id', $academic->course_id)
                ->where('semester', $this->nextSemester)
                ->where('paper_type', 'DSE')
                ->where('status', 'Active')
                ->count();

            $validGeCount = Paper::whereIn('id', $geIds)
                ->where('dept_id', '!=', $academic->department_id)
                ->where('semester', $this->nextSemester)
                ->where('paper_type', 'GE')
                ->where('status', 'Active')
                ->count();

            if ($validDseCount !== count($dseIds)) {
                $this->addError('selectedPapers.DSE', 'Select valid DSE subjects for your department and course.');
                return;
            }

            if ($validGeCount !== count($geIds)) {
                $this->addError('selectedPapers.GE', 'Select GE subjects from another department.');
                return;
            }
        }

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

            if ($this->ruleSemester() !== 7) {
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
            }

            /*
            |--------------------------------------------------------------------------
            | SAVE ALL SELECTED PAPERS
            |--------------------------------------------------------------------------
            */

            foreach ($this->selectedPapers as $type => $value) {

                if ($this->ruleSemester() === 7 && !in_array($type, ['DSE', 'GE'], true)) {
                    continue;
                }

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
                'ruleSemester' => $this->ruleSemester(),
            ]
        );
    }
}