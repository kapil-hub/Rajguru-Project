<?php

namespace App\Http\Controllers;

use App\Models\LateHeldRequest;
use App\Models\PaperTimetable;
use App\Models\RoleAssignment;
use App\Models\Student;
use App\Models\TimetableHeldPool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OutstandingActionController extends Controller
{
    public function index()
    {
        $teacher = auth('teacher')->user();
        $admin = auth('admin')->user();

        $teacherSlots = collect();
        $myRequests = collect();
        $pendingRequests = collect();

        if ($teacher) {
            $teacherSlots = PaperTimetable::with(['paper', 'course', 'room'])
                ->where('teacher_id', $teacher->id)
                ->orderBy('day_name')
                ->orderBy('start_time')
                ->get();

            $myRequests = LateHeldRequest::with(['timetable.paper', 'timetable.course'])
                ->where('teacher_id', $teacher->id)
                ->latest()
                ->get();

            if ($teacher->hasRole('TIC')) {
                $pendingRequests = LateHeldRequest::with(['teacher', 'timetable.paper', 'timetable.course'])
                    ->where('status', 'pending')
                    ->where(function ($query) use ($teacher) {
                        $query->where('department_id', $teacher->department_id)
                            ->orWhereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('department_id', $teacher->department_id));
                    })
                    ->latest()
                    ->get();

                LateHeldRequest::whereIn('id', $pendingRequests->pluck('id'))
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        if ($admin) {
            $pendingRequests = collect();
        }

        return view('pages.outstanding-actions.index', compact(
            'teacherSlots',
            'myRequests',
            'pendingRequests'
        ));
    }

    public function storeLateHeldRequest(Request $request)
    {
        $teacher = auth('teacher')->user();

        $data = $request->validate([
            'paper_timetable_id' => 'required|exists:paper_timetables,id',
            'held_date' => 'required|date|before_or_equal:today',
            'reason' => 'nullable|string|max:1000',
        ]);

        $slot = PaperTimetable::findOrFail($data['paper_timetable_id']);

        if ((int) $slot->teacher_id !== (int) $teacher->id) {
            abort(403);
        }

        if (strtolower($slot->day_name) !== strtolower(\Carbon\Carbon::parse($data['held_date'])->format('l'))) {
            return back()->with('error', 'Selected date does not match the timetable slot day.');
        }

        $duplicate = LateHeldRequest::where('teacher_id', $teacher->id)
            ->where('paper_timetable_id', $slot->id)
            ->whereDate('held_date', $data['held_date'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'A request for this slot and date already exists.');
        }

        $lateRequest = LateHeldRequest::create([
            'teacher_id' => $teacher->id,
            'department_id' => $teacher->department_id,
            'paper_timetable_id' => $slot->id,
            'held_date' => $data['held_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        $this->mailTic($lateRequest, 'New late held class request', 'A late held class request is waiting for your approval.');

        return back()->with('success', 'Late held request submitted to TIC.');
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

        $this->mailTeacher($lateHeldRequest, 'Late held request approved', 'Your late held request was approved and added to the held pool.');

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

        $this->mailTeacher($lateHeldRequest, 'Late held request rejected', 'Your late held request was rejected.');

        return back()->with('success', 'Request rejected.');
    }

    private function authorizeAction(LateHeldRequest $lateHeldRequest): void
    {
        if (auth('admin')->check()) {
            abort(403);
        }

        $teacher = auth('teacher')->user();
        $requesterDepartmentId = $lateHeldRequest->teacher?->department_id;

        if (!$teacher || !$teacher->hasRole('TIC')) {
            abort(403);
        }

        if ((int) $teacher->department_id !== (int) $lateHeldRequest->department_id
            && (int) $teacher->department_id !== (int) $requesterDepartmentId) {
            abort(403);
        }
    }

    private function incrementHeldPool(LateHeldRequest $lateHeldRequest): void
    {
        $slot = $lateHeldRequest->timetable;
        $date = $lateHeldRequest->held_date;
        $marker = $date->toDateString() . ':' . $slot->id;

        $alreadyMarked = TimetableHeldPool::where('teacher_id', $lateHeldRequest->teacher_id)
            ->where('course_id', $slot->course_id)
            ->where('semester_id', $slot->semester)
            ->where('paper_master_id', $slot->paper_id)
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

    private function mailTic(LateHeldRequest $lateHeldRequest, string $subject, string $line): void
    {
        $ticRoleIds = \App\Models\Role::where('name', 'TIC')->pluck('id');
        $ticIds = RoleAssignment::where('auth_type', 'teacher')->whereIn('role_id', $ticRoleIds)->pluck('auth_id');
        $departmentId = $lateHeldRequest->teacher?->department_id ?? $lateHeldRequest->department_id;

        $emails = \App\Models\Teacher::whereIn('id', $ticIds)
            ->where('department_id', $departmentId)
            ->pluck('email')
            ->filter()
            ->all();

        $this->sendMail($emails, $subject, $line);
    }

    private function mailTeacher(LateHeldRequest $lateHeldRequest, string $subject, string $line): void
    {
        $this->sendMail([$lateHeldRequest->teacher?->email], $subject, $line);
    }

    private function sendMail(array $emails, string $subject, string $line): void
    {
        if (app()->environment('local')) {
            $emails = ['kapil.kumar@sol-du.ac.in'];
        }

        foreach (array_filter($emails) as $email) {
            try {
                Mail::raw($line, fn ($message) => $message->to($email)->subject($subject));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
