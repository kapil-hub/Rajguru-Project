<?php

namespace App\Livewire\Admin;

use App\Models\FacultyDetail;
use App\Models\Paper;
use App\Models\StudentAcademic;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\StudentSubjectRegistrationForm;
use Auth;

class RegistrationManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    public $activeTab = 'students';

    public $paperTypeFilter = '';

    public $paperFilter = '';

    public $analyticsSemester = '';
    public $search = '';

    public $semester = '';

    public $showModal = false;

    public $studentSubjects = [];

    public $selectedStudent = null;

    /*
    |--------------------------------------------------------------------------
    | MAIN QUERY
    |--------------------------------------------------------------------------
    */

    public function getRegistrationsProperty()
    {

    $sameDepartMentStudentIds = [];
    if(Auth::guard('teacher')->check() && auth()->user()->hasRole("TIC")){
        $departmentId = Teacher::with('details')->where('id',auth()->user()->id)->first();
        if($departmentId && !empty($departmentId->details)){
            $deptIds = $departmentId->details->pluck("department_id")->toArray();
            $sameDepartMentStudentIds = StudentAcademic::whereIn("department_id",$deptIds)->get()->pluck("student_user_id")->toArray();
        }
    }

        $data = StudentSubjectRegistrationForm::query()

    ->with('student.academic')

    ->select(
        'student_user_id',
        'semester',

        DB::raw('COUNT(*) as total_subjects'),

        DB::raw('SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending_count'),

        DB::raw('SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved_count'),

        DB::raw('MAX(created_at) as latest_created_at')
    )

    ->when($this->search, function ($q) {

        $q->whereHas('student', function ($sub) {

            $sub->where('name', 'like', '%' . $this->search . '%')

                ->orWhereHas('academic', function ($subsss) {

                    $subsss->where(
                        'roll_number',
                        'like',
                        '%' . $this->search . '%'
                    );

                });

        });

    })

    ->when($this->semester, function ($q) {

        $q->where('semester', $this->semester);

    })

    ->when(!empty($sameDepartMentStudentIds), function ($q) use ($sameDepartMentStudentIds) {

        $q->whereIn(
            'student_user_id',
            $sameDepartMentStudentIds
        );

    })

    ->groupBy(
        'student_user_id',
        'semester'
    )

    ->orderByDesc('latest_created_at')

    ->paginate(15);

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW STUDENT SUBJECTS
    |--------------------------------------------------------------------------
    */

    public function view($studentId, $semester)
    {
        $this->selectedStudent = StudentSubjectRegistrationForm::with([
            'student'
        ])

        ->where('student_user_id', $studentId)
        ->where('semester', $semester)
        ->first();

        $this->studentSubjects =
            StudentSubjectRegistrationForm::with('paper')

            ->where('student_user_id', $studentId)

            ->where('semester', $semester)

            ->get();

        $this->showModal = true;
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE SINGLE SUBJECT
    |--------------------------------------------------------------------------
    */

    public function approveSubject($id)
    {
        StudentSubjectRegistrationForm::where(
            'id',
            $id
        )->update([
            'is_approved' => 1
        ]);

        foreach ($this->studentSubjects as $subject) {

            if ($subject->id == $id) {

                $subject->is_approved = 1;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT SINGLE SUBJECT
    |--------------------------------------------------------------------------
    */

    public function rejectSubject($id)
    {
        StudentSubjectRegistrationForm::where(
            'id',
            $id
        )->update([
            'is_approved' => 2
        ]);

        foreach ($this->studentSubjects as $subject) {

            if ($subject->id == $id) {

                $subject->is_approved = 2;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE ALL
    |--------------------------------------------------------------------------
    */

    public function approveAll()
    {
        if (!$this->selectedStudent) {
            return;
        }

        StudentSubjectRegistrationForm::where(
            'student_user_id',
            $this->selectedStudent->student_user_id
        )

        ->where(
            'semester',
            $this->selectedStudent->semester
        )

        ->update([
            'is_approved' => 1
        ]);

        foreach ($this->studentSubjects as $subject) {

            $subject->is_approved = 1;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT ALL
    |--------------------------------------------------------------------------
    */

    public function rejectAll()
    {
        if (!$this->selectedStudent) {
            return;
        }

        StudentSubjectRegistrationForm::where(
            'student_user_id',
            $this->selectedStudent->student_user_id
        )

        ->where(
            'semester',
            $this->selectedStudent->semester
        )

        ->update([
            'is_approved' => 2
        ]);

        foreach ($this->studentSubjects as $subject) {

            $subject->is_approved = 2;
        }
    }


    public function getPaperAnalyticsProperty()
{
    return StudentSubjectRegistrationForm::query()

        ->with('paper')

        ->select(
            'paper_master_id',

            DB::raw('COUNT(DISTINCT student_user_id) as total_students'),

            DB::raw('SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved_count'),

            DB::raw('SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending_count'),

            DB::raw('SUM(CASE WHEN is_approved = 2 THEN 1 ELSE 0 END) as rejected_count')
        )

        ->when($this->paperFilter, function ($q) {

            $q->where('paper_master_id', $this->paperFilter);

        })

        ->when($this->analyticsSemester, function ($q) {

            $q->where('semester', $this->analyticsSemester);

        })

        ->when($this->paperTypeFilter, function ($q) {

            $q->whereHas('paper', function ($sub) {

                $sub->where(
                    'paper_type',
                    $this->paperTypeFilter
                );

            });

        })

        ->groupBy('paper_master_id')

        ->paginate(15);
}

    public function render()
    {
        return view(
            'livewire.admin.registration-management',
            [

                'registrations' => $this->registrations,

                'paperAnalytics' => $this->paperAnalytics,

                'papers' => Paper::orderBy('name')->get()

            ]
        );
    }
}