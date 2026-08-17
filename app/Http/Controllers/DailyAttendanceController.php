<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StudentDailyAttendance;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\PaperTimetable;
use Carbon\Carbon;

class DailyAttendanceController extends Controller
{
    public function fillAttendance($assignmentId, $month, $year)
    {
        if ($this->isFutureMonth((int) $month, (int) $year)) {
            return redirect()
                ->route('teacher.attendance.pending')
                ->with('error', 'Future month attendance cannot be filled.');
        }

        $assignment = \App\Models\TimetableHeldPool::findOrFail($assignmentId);
        $slots = PaperTimetable::where('teacher_id', $assignment->teacher_id)
            ->where('course_id', $assignment->course_id)
            ->where('semester', $assignment->semester_id)
            ->where('paper_id', $assignment->paper_master_id)
            ->get()
            ->filter(fn ($slot) => $this->batchIdentifier($slot) === (string) ($assignment->batch_identifier ?? ''));

        $assignment->has_lecture_slot = $slots->contains(fn ($slot) => (bool) $slot->is_lecture);
        $assignment->has_tute_slot = $slots->contains(fn ($slot) => (bool) $slot->is_tutorial);
        $assignment->has_practical_slot = $slots->contains(fn ($slot) => (bool) $slot->is_practical);

        $studentIds = is_array($assignment->student_ids) 
            ? $assignment->student_ids 
            : (json_decode($assignment->student_ids ?? '[]', true) ?? []);

        $students = Student::with('academic')
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get();

        return view(
            'pages.teacher.attendance.daily',
            compact('assignment', 'students', 'month', 'year')
        );
    }

    public function store(Request $request)
    {

        $students = Student::whereHas('academic', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            })
            ->whereHas('papers', function ($q) use ($request) {
                $q->where('paper_master_id', $request->paper_master_id);
            })
            ->orderBy('name')
            ->get();
        foreach ($students as $s) {
            $lecture   = $request->has("attendance.{$s->id}.lecture") ? 1 : 0;
            $tute      = $request->has("attendance.{$s->id}.tute") ? 1 : 0;
            $practical = $request->has("attendance.{$s->id}.practical") ? 1 : 0;

            StudentDailyAttendance::updateOrCreate(
                [
                    'student_id' => $s->id,
                    'attendance_date' => $request->attendance_date,
                    'paper_master_id' => $request->paper_master_id,
                ],
                [
                    'teacher_id' => auth('teacher')->id(),
                    'course_id' => $request->course_id,
                    'semester_id' => $request->semester_id,
                    'lecture' => $lecture,
                    'tute' => $tute,
                    'practical' => $practical,
                ]
            );
        }

        return back()->with('success', 'Daily attendance saved successfully.');
    }

    private function batchIdentifier(PaperTimetable $slot): string
    {
        if (!$this->hasSlotBatches($slot)) {
            return '';
        }

        $batches = $this->slotBatches($slot);
        sort($batches, SORT_NATURAL | SORT_FLAG_CASE);

        return implode(',', $batches);
    }

    private function slotBatches(PaperTimetable $slot): array
    {
        if (blank($slot->batches)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($batch) => strtoupper(trim($batch)),
            explode(',', $slot->batches)
        ))));
    }

    private function hasSlotBatches(PaperTimetable $slot): bool
    {
        return !empty($this->slotBatches($slot));
    }

    private function isFutureMonth(int $month, int $year): bool
    {
        return Carbon::createFromDate($year, $month, 1)->startOfMonth()
            ->greaterThan(now()->startOfMonth());
    }
}
