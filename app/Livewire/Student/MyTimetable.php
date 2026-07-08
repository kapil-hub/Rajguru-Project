<?php

namespace App\Livewire\Student;

use App\Models\Paper;
use App\Models\PaperTimetable;
use App\Models\TimetableSlot;
use Livewire\Component;

class MyTimetable extends Component
{
    public $paper_id = '';
    public $day_name = '';
    public $slot_type = '';

    public $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    public $timeSlots = [];

    public function mount()
    {
        $this->timeSlots = TimetableSlot::orderBy('start_time')
            ->get()
            ->map(fn ($slot) => $slot->formatted_slot)
            ->toArray();
    }

    public function render()
    {
        $student = auth('student')->user()?->load(['academic.course', 'papers.paper']);
        $academic = $student?->academic;

        if (!$student || !$academic) {
            return view('livewire.student.my-timetable', [
                'student' => $student,
                'academic' => $academic,
                'papers' => collect(),
                'timetableGrid' => [],
            ]);
        }

        $studentPapers = $student->papers()
            ->with('paper')
            ->where('is_backlog', 0)
            ->get();

        $paperIds = $studentPapers->pluck('paper_master_id')->unique()->values();
        $papers = Paper::whereIn('id', $paperIds)->orderBy('name')->get();
        $studentBatchByPaper = $studentPapers
            ->mapWithKeys(fn ($studentPaper) => [
                $studentPaper->paper_master_id => strtoupper(trim((string) $studentPaper->batch)),
            ]);

        $query = PaperTimetable::with(['paper', 'room', 'teacher', 'course'])
            ->whereIn('paper_id', $paperIds)
            ->where('semester', $academic->current_semester)
            ->where(function ($query) use ($academic) {
                $query->where('course_id', $academic->course_id)
                    ->orWhereHas('paper', function ($paperQuery) {
                        $paperQuery->whereNotIn('paper_type', ['DSC', 'DSE']);
                    });
            });

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

        $slots = $query->orderBy('day_name')->orderBy('start_time')->get()
            ->filter(function ($slot) use ($studentBatchByPaper) {
                if (empty($slot->batches)) {
                    return true;
                }

                $studentBatch = $studentBatchByPaper->get($slot->paper_id);
                if (!$studentBatch) {
                    return false;
                }

                $slotBatches = collect(explode(',', $slot->batches))
                    ->map(fn ($batch) => strtoupper(trim($batch)))
                    ->filter();

                return $slotBatches->contains($studentBatch);
            });

        $timetableGrid = [];
        foreach ($slots as $slot) {
            $timeKey = substr($slot->start_time, 0, 5) . '-' . substr($slot->end_time, 0, 5);
            $key = $slot->day_name . '|' . $timeKey;
            $timetableGrid[$key][] = $slot;
        }

        return view('livewire.student.my-timetable', [
            'student' => $student,
            'academic' => $academic,
            'papers' => $papers,
            'timetableGrid' => $timetableGrid,
        ]);
    }
}
