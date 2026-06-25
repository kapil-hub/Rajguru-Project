<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Models\Courses;
use App\Models\Paper;
use App\Models\Room;
use App\Models\PaperTimetable;
use App\Models\TimetableSlot;

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
            'timetableGrid' => $timetableGrid
        ]);
    }
}
