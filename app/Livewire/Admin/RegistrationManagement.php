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

    public $showMigrationPanel = false;

    public $migrationLogs = [];

    public $migrationError = null;

    public $migrationSuccess = false;

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

    private function addMigrationLog(string $message): void
    {
        $this->migrationLogs[] = $message;
    }

    public function migrateRegistrations()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $this->showMigrationPanel = true;
        $this->migrationLogs = [];
        $this->migrationError = null;
        $this->migrationSuccess = false;

        try {
            DB::transaction(function () {
                $chunkSize = 500;

                $this->addMigrationLog('Step 1: Reading all current student papers.');

                $existingStudentPapers = DB::table('student_papers')->get();
                $this->addMigrationLog('Step 2: Archiving ' . $existingStudentPapers->count() . ' student paper row(s) into student_papers_history.');

                if ($existingStudentPapers->isNotEmpty()) {
                    $existingStudentPapers->chunk($chunkSize)->each(function ($papers) {
                        DB::table('student_papers_history')->insert(
                            $papers->map(function ($paper) {
                                return [
                                    'original_student_paper_id' => $paper->id,
                                    'student_user_id' => $paper->student_user_id,
                                    'paper_master_id' => $paper->paper_master_id,
                                    'semester' => $paper->semester,
                                    'academic_year' => $paper->academic_year,
                                    'is_backlog' => $paper->is_backlog,
                                    'created_at' => $paper->created_at,
                                    'updated_at' => $paper->updated_at,
                                    'archived_at' => now(),
                                ];
                            })->all()
                        );
                    });
                }

                $this->addMigrationLog('Step 3: Clearing student_papers.');
                DB::table('student_papers')->delete();

                $approvedRegistrations = StudentSubjectRegistrationForm::query()
                    ->with('paper')
                    ->where('is_approved', 1)
                    ->orderBy('student_user_id')
                    ->orderBy('semester')
                    ->get();

                $this->addMigrationLog('Step 4: Importing ' . $approvedRegistrations->count() . ' approved registration row(s) into student_papers.');

                if ($approvedRegistrations->isNotEmpty()) {
                    $approvedRegistrations->chunk($chunkSize)->each(function ($registrations) {
                        $now = now();

                        DB::table('student_papers')->insert(
                            $registrations->map(function ($registration) use ($now) {
                                return [
                                    'student_user_id' => $registration->student_user_id,
                                    'paper_master_id' => $registration->paper_master_id,
                                    'semester' => $registration->semester,
                                    'academic_year' => $registration->academic_year,
                                    'is_backlog' => $registration->is_backlog,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            })->all()
                        );
                    });
                }

                $this->addMigrationLog('Step 5: Building student academic migration data.');

                $academicMigrationData = $approvedRegistrations
                    ->groupBy('student_user_id')
                    ->map(function ($items) {
                        $latestItem = $items->sortByDesc('semester')->first();

                        return [
                            'current_semester' => (int) $items->max(function ($item) {
                                return (int) $item->semester;
                            }),
                            'current_academic_year' => $latestItem->academic_year ?? null,
                        ];
                    });

                $academicRows = StudentAcademic::all();

                $this->addMigrationLog('Step 6: Archiving ' . $academicRows->count() . ' student academic row(s) into student_academic_history.');

                if ($academicRows->isNotEmpty()) {
                    $academicRows->chunk($chunkSize)->each(function ($rows) {
                        DB::table('student_academic_history')->insert(
                            $rows->map(function ($row) {
                                return [
                                    'original_student_academic_id' => $row->id,
                                    'student_user_id' => $row->student_user_id,
                                    'roll_number' => $row->roll_number,
                                    'college_roll_number' => $row->college_roll_number,
                                    'department_id' => $row->department_id,
                                    'course_id' => $row->course_id,
                                    'current_semester' => $row->current_semester,
                                    'section' => $row->section,
                                    'current_academic_year' => $row->current_academic_year,
                                    'created_at' => $row->created_at,
                                    'updated_at' => $row->updated_at,
                                    'archived_at' => now(),
                                ];
                            })->all()
                        );
                    });
                }

                $this->addMigrationLog('Step 7: Clearing current student academic records.');
                StudentAcademic::query()->delete();

                $this->addMigrationLog('Step 8: Recreating student academic rows from registration data.');

                $academicRowsToRestore = $academicRows->filter(function ($row) use ($academicMigrationData) {
                    return $academicMigrationData->has($row->student_user_id);
                });

                $this->addMigrationLog('Step 8: Restoring ' . $academicRowsToRestore->count() . ' approved student academic row(s).');

                if ($academicRowsToRestore->isNotEmpty()) {
                    $academicRowsToRestore->each(function ($row) use ($academicMigrationData) {
                        $migrationData = $academicMigrationData->get($row->student_user_id, []);

                        StudentAcademic::create([
                            'student_user_id' => $row->student_user_id,
                            'roll_number' => $row->roll_number,
                            'college_roll_number' => $row->college_roll_number,
                            'department_id' => $row->department_id,
                            'course_id' => $row->course_id,
                            'current_semester' => $migrationData['current_semester'] ?? $row->current_semester,
                            'section' => $row->section,
                            'current_academic_year' => $migrationData['current_academic_year'] ?? $row->current_academic_year,
                        ]);
                    });
                }

                $this->addMigrationLog('Step 9: Registration migration completed successfully.');
            });

            $this->migrationSuccess = true;
        } catch (\Throwable $e) {
            $this->migrationError = $e->getMessage();
            $this->addMigrationLog('Migration failed and no changes were committed.');
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