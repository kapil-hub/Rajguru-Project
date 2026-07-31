<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Models\Courses;
use App\Models\Paper;
use App\Models\Room;
use App\Models\PaperTimetable;
use App\Models\TimetableSlot;
use App\Models\Student;
use App\Models\TimetableHeldPool;

class MyTimetable extends Component
{
    public $course_id = '';
    public $semester = '';
    public $paper_id = '';
    public $day_name = '';
    public $slot_type = ''; // 'lecture', 'tutorial', 'practical'
    
    public $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    public $timeSlots = [];

    public function mount()
    {
        $this->timeSlots = TimetableSlot::orderBy('start_time')
            ->get()
            ->map(fn($s) => $s->formatted_slot)
            ->toArray();
    }

    public function render()
    {
        $teacherId = auth('teacher')->id();

        // Get filter options from teacher's own assigned slots
        $assignedSlots = PaperTimetable::where('teacher_id', $teacherId)->get();

        $courses = Courses::whereIn('id', $assignedSlots->pluck('course_id')->unique())->get();
        $semesters = $assignedSlots->pluck('semester')->unique()->sort();
        $papers = Paper::whereIn('id', $assignedSlots->pluck('paper_id')->unique())->get();

        // Query the timetables to display
        $query = PaperTimetable::with(['paper', 'room', 'course', 'department'])
            ->where('teacher_id', $teacherId);

        if ($this->course_id) {
            $query->where('course_id', $this->course_id);
        }
        if ($this->semester) {
            $query->where('semester', $this->semester);
        }
        if ($this->paper_id) {
            $query->where('paper_id', $this->paper_id);
        }
        if ($this->day_name) {
            $query->where('day_name', $this->day_name);
        }
        if ($this->slot_type) {
            if ($this->slot_type === 'lecture') {
                $query->where('is_lecture', true);
            } elseif ($this->slot_type === 'tutorial') {
                $query->where('is_tutorial', true);
            } elseif ($this->slot_type === 'practical') {
                $query->where('is_practical', true);
            }
        }

        $slots = $query->get();
        $todayMarkerPrefix = now()->toDateString() . ':';

        $markedTodaySlotIds = TimetableHeldPool::where('teacher_id', $teacherId)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->get(['marked_slots'])
            ->flatMap(fn ($pool) => $pool->marked_slots ?? [])
            ->filter(fn ($marker) => str_starts_with($marker, $todayMarkerPrefix))
            ->map(fn ($marker) => (int) substr($marker, strlen($todayMarkerPrefix)))
            ->unique()
            ->values()
            ->all();

        // Group slots for easy grid rendering: Day | TimeSlot
        $timetableGrid = [];
        foreach ($slots as $slot) {
            $timeKey = substr($slot->start_time, 0, 5) . '-' . substr($slot->end_time, 0, 5);
            $key = $slot->day_name . '|' . $timeKey;
            $timetableGrid[$key][] = $slot;
        }

        return view('livewire.teacher.my-timetable', [
            'courses' => $courses,
            'semesters' => $semesters,
            'papers' => $papers,
            'timetableGrid' => $timetableGrid,
            'markedTodaySlotIds' => $markedTodaySlotIds,
        ]);
    }

    public function markHeld($slotId)
    {
        $slot = PaperTimetable::findOrFail($slotId);
        $teacherId = auth('teacher')->id();

        if ((int) $slot->teacher_id !== (int) $teacherId) {
            abort(403);
        }

        // Verify the slot is for today
        if (strtolower($slot->day_name) !== strtolower(now()->format('l'))) {
            session()->flash('error', 'You can only mark today\'s classes as held.');
            return;
        }

        // Fetch students matching this slot
        $paper_id = $slot->paper_id;
        $course_id = $slot->course_id;
        $month = now()->month;
        $year = now()->year;
        $todaySlotMarker = now()->toDateString() . ':' . $slot->id;
        $batchIdentifier = $this->batchIdentifier($slot);

        $alreadyMarked = TimetableHeldPool::where('teacher_id', $teacherId)
            ->where('course_id', $course_id)
            ->where('semester_id', $slot->semester)
            ->where('paper_master_id', $paper_id)
            ->where('batch_identifier', $batchIdentifier)
            ->where('month', $month)
            ->where('year', $year)
            ->get(['marked_slots'])
            ->contains(function ($pool) use ($todaySlotMarker) {
                return in_array($todaySlotMarker, $pool->marked_slots ?? [], true);
            });

        if ($alreadyMarked) {
            session()->flash('error', 'This slot is already marked as held for today.');
            return;
        }

        $studentsQuery = Student::with('academic')->where(function ($q) use ($paper_id, $course_id) {
            // Case 1: DSC / DSE → course required
            $q->whereHas('papers', function ($p) use ($paper_id) {
                    $p->where('paper_master_id', $paper_id)
                        ->whereHas('paper', function ($pm) {
                            $pm->whereIn('paper_type', ['DSC', 'DSE']);
                        })
                        ->where("is_backlog", 0);
                })
                ->whereHas('academic', function ($a) use ($course_id) {
                    $a->where('course_id', $course_id);
                });

            // Case 2: Other paper types → ignore course
            $q->orWhereHas('papers', function ($p) use ($paper_id) {
                $p->where('paper_master_id', $paper_id)
                ->whereHas('paper', function ($pm) {
                    $pm->whereNotIn('paper_type', ['DSC', 'DSE']);
                });
            });
        });

        // Batch-wise slots must only include students registered in this slot's batch.
        if ($this->hasSlotBatches($slot)) {
            $batches = $this->slotBatches($slot);
            $studentsQuery->whereHas('papers', function ($p) use ($paper_id, $batches) {
                $p->where('paper_master_id', $paper_id)
                  ->whereIn(\DB::raw('UPPER(TRIM(batch))'), $batches);
            });
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            session()->flash('error', 'No students found registered for this slot.');
            return;
        }

        // Group students by section
        $groupedStudents = $students->groupBy(function ($student) {
            $section = strtoupper(trim((string) ($student->academic->section ?? '')));

            return $section !== '' ? $section : 'A';
        });

        foreach ($groupedStudents as $section => $sectionStudents) {
            $studentIds = $sectionStudents->pluck('id')->toArray();

            $poolAttributes = [
                'teacher_id' => $teacherId,
                'course_id' => $course_id,
                'semester_id' => $slot->semester,
                'paper_master_id' => $paper_id,
                'batch_identifier' => $batchIdentifier,
                'month' => $month,
                'year' => $year,
            ];

            $pool = TimetableHeldPool::where($poolAttributes)
                ->where(function ($query) use ($section) {
                    $query->where('section', $section);

                    if ($section === 'A') {
                        $query->orWhereNull('section')
                            ->orWhere('section', '');
                    }
                })
                ->first();

            if (!$pool) {
                $pool = new TimetableHeldPool($poolAttributes);
            }

            $pool->section = $section;

            // Update held counts based on slot type
            if ($slot->is_lecture) {
                $pool->lecture_held = ($pool->lecture_held ?? 0) + 1;
            }
            if ($slot->is_tutorial) {
                $pool->tute_held = ($pool->tute_held ?? 0) + 1;
            }
            if ($slot->is_practical) {
                $pool->practical_held = ($pool->practical_held ?? 0) + 1;
            }

            // Merge student IDs
            $existingStudentIds = is_array($pool->student_ids) 
                ? $pool->student_ids 
                : (json_decode($pool->student_ids ?? '[]', true) ?? []);
            
            $mergedStudentIds = array_values(array_unique(array_merge($existingStudentIds, $studentIds)));
            $pool->student_ids = $mergedStudentIds;

            $existingMarkedSlots = is_array($pool->marked_slots)
                ? $pool->marked_slots
                : (json_decode($pool->marked_slots ?? '[]', true) ?? []);

            $pool->marked_slots = array_values(array_unique(array_merge($existingMarkedSlots, [$todaySlotMarker])));

            $pool->save();
        }

        session()->flash('success', 'Class marked as held and added/updated in pool.');
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
}
