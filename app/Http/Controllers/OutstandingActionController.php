<?php

namespace App\Http\Controllers;

use App\Models\LateHeldRequest;
use App\Models\PaperTimetable;
use App\Models\RoleAssignment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TimetableHeldPool;
use App\Jobs\SendLateHeldRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutstandingActionController extends Controller
{
    public function index()
    {
        $teacher = auth('teacher')->user();
        $admin = auth('admin')->user();

        $myRequests = collect();
        $pendingRequests = collect();

        if ($teacher) {
            $myRequests = LateHeldRequest::with(['teacher', 'substituteTeacher', 'timetable.paper', 'timetable.course', 'timetable.room', 'timetable.teacher'])
                ->where(function ($query) use ($teacher) {
                    $query->where('teacher_id', $teacher->id)
                        ->orWhere('substitute_teacher_id', $teacher->id);
                })
                ->latest()
                ->get();

            if ($this->canApproveOutstandingRequests($teacher)) {
                $pendingRequests = LateHeldRequest::with(['teacher', 'substituteTeacher', 'timetable.paper', 'timetable.course', 'timetable.room', 'timetable.teacher'])
                    ->where('status', 'pending')
                    ->when(!$this->canApproveAllOutstandingRequests($teacher), fn ($query) => $this->scopePendingRequestsToTeacherDepartment($query, $teacher))
                    ->latest()
                    ->get();

                LateHeldRequest::whereIn('id', $pendingRequests->pluck('id'))
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        if ($admin) {
            $pendingRequests = LateHeldRequest::with(['teacher', 'substituteTeacher', 'timetable.paper', 'timetable.course', 'timetable.room', 'timetable.teacher'])
                ->where('status', 'pending')
                ->latest()
                ->get();
        }

        return view('pages.outstanding-actions.index', compact(
            'myRequests',
            'pendingRequests'
        ));
    }

    public function createLateHeldRequest()
    {
        $teacher = auth('teacher')->user();

        if (!$teacher) {
            abort(403);
        }

        $teacherSlots = PaperTimetable::with(['paper', 'course', 'room'])
            ->where('teacher_id', $teacher->id)
            ->orderByRaw("FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get();

        return view('pages.outstanding-actions.create-late-held', compact('teacherSlots'));
    }

    public function createSubstituteHeldRequest()
    {
        $teacher = auth('teacher')->user();

        if (!$teacher) {
            abort(403);
        }

        $teacherSlots = PaperTimetable::with(['paper', 'course', 'room', 'teacher'])
            ->where('teacher_id', '!=', $teacher->id)
            ->where(function ($query) use ($teacher) {
                $query->where('department_id', $teacher->department_id)
                    ->orWhereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('department_id', $teacher->department_id));
            })
            ->orderByRaw("FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get();

        $teachers = Teacher::whereIn('id', $teacherSlots->pluck('teacher_id')->unique())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.outstanding-actions.create-substitute-held', compact('teacherSlots', 'teachers'));
    }

    public function storeLateHeldRequest(Request $request)
    {
        $teacher = auth('teacher')->user();

        if (!$teacher) {
            abort(403);
        }

        $data = $request->validate([
            'paper_timetable_id' => 'required|exists:paper_timetables,id',
            'held_date' => 'required|date|before_or_equal:today',
            'reason' => 'required|string|min:10|max:1500',
        ]);

        $slot = PaperTimetable::findOrFail($data['paper_timetable_id']);

        if ((int) $slot->teacher_id !== (int) $teacher->id) {
            abort(403);
        }

        if (strtolower($slot->day_name) !== strtolower(\Carbon\Carbon::parse($data['held_date'])->format('l'))) {
            return back()->withInput()->with('error', 'Selected date does not match the timetable slot day.');
        }

        $duplicate = LateHeldRequest::where('teacher_id', $teacher->id)
            ->where('paper_timetable_id', $slot->id)
            ->whereDate('held_date', $data['held_date'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'A request for this slot and date already exists.');
        }

        $lateRequest = LateHeldRequest::create([
            'teacher_id' => $teacher->id,
            'department_id' => $teacher->department_id,
            'paper_timetable_id' => $slot->id,
            'held_date' => $data['held_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
            'request_type' => 'late_held',
        ]);

        $this->mailTic($lateRequest);

        return redirect()
            ->route('outstanding-actions.index')
            ->with('success', 'Late held request submitted to TIC.');
    }

    public function storeSubstituteHeldRequest(Request $request)
    {
        $teacher = auth('teacher')->user();

        if (!$teacher) {
            abort(403);
        }

        $data = $request->validate([
            'original_teacher_id' => 'required|exists:faculty_users,id',
            'paper_timetable_id' => 'required|exists:paper_timetables,id',
            'held_date' => 'required|date|before_or_equal:today',
            'reason' => 'required|string|min:10|max:1500',
        ]);

        $slot = PaperTimetable::with('teacher')->findOrFail($data['paper_timetable_id']);

        if ((int) $slot->teacher_id === (int) $teacher->id) {
            return back()->withInput()->with('error', 'Please use Late Held Request for your own timetable slot.');
        }

        if ((int) $slot->teacher_id !== (int) $data['original_teacher_id']) {
            return back()->withInput()->with('error', 'Selected timetable slot does not belong to the selected teacher.');
        }

        if (strtolower($slot->day_name) !== strtolower(\Carbon\Carbon::parse($data['held_date'])->format('l'))) {
            return back()->withInput()->with('error', 'Selected date does not match the timetable slot day.');
        }

        $duplicate = LateHeldRequest::where('paper_timetable_id', $slot->id)
            ->whereDate('held_date', $data['held_date'])
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'A request for this slot and date is already pending or approved.');
        }

        $lateRequest = LateHeldRequest::create([
            'teacher_id' => $slot->teacher_id,
            'substitute_teacher_id' => $teacher->id,
            'department_id' => $slot->department_id ?: $slot->teacher?->department_id,
            'paper_timetable_id' => $slot->id,
            'held_date' => $data['held_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
            'request_type' => 'substitute_held',
        ]);

        $this->mailTic($lateRequest);

        return redirect()
            ->route('outstanding-actions.index')
            ->with('success', 'Substitute lecture request submitted to TIC.');
    }

    public function approve(Request $request, LateHeldRequest $lateHeldRequest)
    {
        $this->authorizeAction($lateHeldRequest);

        if ($lateHeldRequest->status !== 'pending') {
            return back()->with('error', 'This request is already actioned.');
        }

        DB::transaction(function () use ($request, $lateHeldRequest) {
            $this->incrementHeldPool($lateHeldRequest);

            $lateHeldRequest->update([
                'status' => 'approved',
                'tic_remark' => $request->input('tic_remark'),
                'action_by' => auth('teacher')->id(),
                'action_at' => now(),
            ]);
        });

        $this->mailTeacher($lateHeldRequest, 'approved');

        return back()->with('success', 'Request approved and held pool updated.');
    }

    public function reject(Request $request, LateHeldRequest $lateHeldRequest)
    {
        $this->authorizeAction($lateHeldRequest);

        if ($lateHeldRequest->status !== 'pending') {
            return back()->with('error', 'This request is already actioned.');
        }

        $lateHeldRequest->update([
            'status' => 'rejected',
            'tic_remark' => $request->input('tic_remark'),
            'action_by' => auth('teacher')->id(),
            'action_at' => now(),
        ]);

        $this->mailTeacher($lateHeldRequest, 'rejected');

        return back()->with('success', 'Request rejected.');
    }

    private function authorizeAction(LateHeldRequest $lateHeldRequest): void
    {
        if (auth('admin')->check()) {
            return;
        }

        $teacher = auth('teacher')->user();
        $requesterDepartmentId = $lateHeldRequest->teacher?->department_id;
        $substituteDepartmentId = $lateHeldRequest->substituteTeacher?->department_id;

        if (!$this->canApproveOutstandingRequests($teacher)) {
            abort(403);
        }

        if (!$this->canApproveAllOutstandingRequests($teacher)
            && (int) $teacher->department_id !== (int) $lateHeldRequest->department_id
            && (int) $teacher->department_id !== (int) $requesterDepartmentId
            && (int) $teacher->department_id !== (int) $substituteDepartmentId) {
            abort(403);
        }
    }

    private function incrementHeldPool(LateHeldRequest $lateHeldRequest): void
    {
        $slot = $lateHeldRequest->timetable;
        $date = $lateHeldRequest->held_date;
        $marker = $date->toDateString() . ':' . $slot->id;
        $batchIdentifier = $this->batchIdentifier($slot);

        $alreadyMarked = TimetableHeldPool::where('teacher_id', $lateHeldRequest->teacher_id)
            ->where('course_id', $slot->course_id)
            ->where('semester_id', $slot->semester)
            ->where('paper_master_id', $slot->paper_id)
            ->where('batch_identifier', $batchIdentifier)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->get(['marked_slots'])
            ->contains(fn ($pool) => in_array($marker, $pool->marked_slots ?? [], true));

        if ($alreadyMarked) {
            return;
        }

        $studentsQuery = Student::with('academic')->where(function ($q) use ($slot) {
            $q->whereHas('papers', function ($p) use ($slot) {
                    $p->where('paper_master_id', $slot->paper_id)
                        ->whereHas('paper', fn ($pm) => $pm->whereIn('paper_type', ['DSC', 'DSE']))
                        ->where('is_backlog', 0);
                })
                ->whereHas('academic', fn ($a) => $a->where('course_id', $slot->course_id));

            $q->orWhereHas('papers', function ($p) use ($slot) {
                $p->where('paper_master_id', $slot->paper_id)
                    ->whereHas('paper', fn ($pm) => $pm->whereNotIn('paper_type', ['DSC', 'DSE']));
            });
        });

        if (!empty($slot->batches)) {
            $batches = array_map('trim', explode(',', $slot->batches));
            $studentsQuery->whereHas('papers', fn ($p) => $p->where('paper_master_id', $slot->paper_id)->whereIn('batch', $batches));
        }

        $students = $studentsQuery->get();

        foreach ($students->groupBy(fn ($student) => strtoupper(trim((string) ($student->academic->section ?? ''))) ?: 'A') as $section => $sectionStudents) {
            $pool = TimetableHeldPool::firstOrNew([
                'teacher_id' => $lateHeldRequest->teacher_id,
                'course_id' => $slot->course_id,
                'semester_id' => $slot->semester,
                'paper_master_id' => $slot->paper_id,
                'section' => $section,
                'batch_identifier' => $batchIdentifier,
                'month' => $date->month,
                'year' => $date->year,
            ]);

            if ($slot->is_lecture) {
                $pool->lecture_held = ($pool->lecture_held ?? 0) + 1;
            }
            if ($slot->is_tutorial) {
                $pool->tute_held = ($pool->tute_held ?? 0) + 1;
            }
            if ($slot->is_practical) {
                $pool->practical_held = ($pool->practical_held ?? 0) + 1;
            }

            $pool->student_ids = array_values(array_unique(array_merge($pool->student_ids ?? [], $sectionStudents->pluck('id')->all())));
            $pool->marked_slots = array_values(array_unique(array_merge($pool->marked_slots ?? [], [$marker])));
            $pool->save();
        }
    }

    private function canApproveOutstandingRequests(?Teacher $teacher): bool
    {
        return $teacher && (
            $teacher->hasRole('TIC')
            || $teacher->hasRole('Timetable Controller')
            || $teacher->hasRole('Timetable Coordinator')
        );
    }

    private function canApproveAllOutstandingRequests(?Teacher $teacher): bool
    {
        return $teacher && (
            $teacher->hasRole('Timetable Controller')
            || $teacher->hasRole('Timetable Coordinator')
        );
    }

    private function scopePendingRequestsToTeacherDepartment($query, Teacher $teacher)
    {
        return $query->where(function ($departmentQuery) use ($teacher) {
            $departmentQuery->where('department_id', $teacher->department_id)
                ->orWhereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('department_id', $teacher->department_id));
        });
    }

    private function batchIdentifier(PaperTimetable $slot): string
    {
        if (!$slot->is_practical || blank($slot->batches)) {
            return '';
        }

        $batches = array_filter(array_map('trim', explode(',', $slot->batches)));
        sort($batches, SORT_NATURAL | SORT_FLAG_CASE);

        return implode(',', $batches);
    }

    private function mailTic(LateHeldRequest $lateHeldRequest): void
    {
        $ticRoleIds = \App\Models\Role::where('name', 'TIC')->pluck('id');
        $ticIds = RoleAssignment::where('auth_type', 'teacher')->whereIn('role_id', $ticRoleIds)->pluck('auth_id');
        $departmentId = $lateHeldRequest->department_id ?? $lateHeldRequest->teacher?->department_id;

        $recipients = \App\Models\Teacher::whereIn('id', $ticIds)
            ->where('department_id', $departmentId)
            ->get(['name', 'email'])
            ->filter(fn ($teacher) => filled($teacher->email))
            ->all();

        $requestLabel = $lateHeldRequest->request_type === 'substitute_held'
            ? 'Substitute lecture request'
            : 'Late held class request';

        $this->sendMail(
            $recipients,
            $requestLabel . ' pending approval',
            $lateHeldRequest,
            'pending_approval'
        );
    }

    private function mailTeacher(LateHeldRequest $lateHeldRequest, string $status): void
    {
        $recipients = collect([$lateHeldRequest->substituteTeacher ?: $lateHeldRequest->teacher, $lateHeldRequest->teacher])
            ->filter()
            ->unique('id')
            ->values()
            ->all();

        $this->sendMail(
            $recipients,
            ($lateHeldRequest->request_type === 'substitute_held' ? 'Substitute lecture request ' : 'Late held class request ') . ucfirst($status),
            $lateHeldRequest,
            $status
        );
    }

    private function sendMail(array $recipients, string $subject, LateHeldRequest $lateHeldRequest, string $mailType): void
    {
        $lateHeldRequest->loadMissing(['teacher', 'substituteTeacher', 'timetable.paper', 'timetable.course', 'timetable.room', 'timetable.teacher', 'actionBy']);
        $senderName = ($lateHeldRequest->substituteTeacher ?: $lateHeldRequest->teacher)?->name ?? 'Teacher';
        $isLocalMail = app()->environment('local');

        foreach (array_filter($recipients) as $recipient) {
            if (!filled($recipient->email)) {
                continue;
            }

            $email = $isLocalMail ? 'kapil.kumar@sol-du.ac.in' : $recipient->email;
            $receiverName = $recipient->name ?? 'User';

            SendLateHeldRequestMail::dispatch(
                $lateHeldRequest->id,
                $mailType,
                $email,
                $receiverName,
                $subject,
                $senderName,
                $isLocalMail,
                $recipient->email
            );
        }
    }
}
