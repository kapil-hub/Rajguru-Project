<?php

namespace App\Livewire\Admin;

use Illuminate\Validation\ValidationException;
use Livewire\Component;
use App\Models\Departments;
use App\Models\Courses;
use App\Models\Paper;
use App\Models\Room;
use App\Models\Teacher;
use App\Models\PaperTimetable;

class TimetableManager extends Component
{
    public $department_id = '';
    public $course_id = '';
    public $semester = '';
    public $paper_id = '';
    public $teacher_id = '';
    public $is_lecture = null,$is_tutorial = null,$is_practical = null,$is_coordinator = null;
    public $courses = [];
    public $semesters = [];
    public $papers = [];
    public $faculty = [];
    public $rooms = [];
    public $occupiedSlots = [];
    public $day_name;
    public $start_time;
    public $end_time;
    public $room_id;
    public $showCalendar = false;
    public $showModal = false;
    public $activeSection = 'create';
    public $calendarMode = 'create';
    public $selectedTimetableId = null;
    public $modalKey = 0;
    public $days = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
];

public $timeSlots = [
    '09:00-10:00',
    '10:00-11:00',
    '11:00-12:00',
    '12:00-01:00',
    '01:00-02:00',
    '02:00-03:00',
    '03:00-04:00',
    '04:00-05:00',
    '05:00-06:00',
];
  protected $listeners = [
    'open-slot-modal'
];

    protected function normalizeTimeValue($time): string
    {
        $time = (string) $time;

        if (strlen($time) === 5) {
            return $time . ':00';
        }

        return $time;
    }

    protected function getPaperStudentCount(): int
    {
        if (! $this->paper_id) {
            return 0;
        }

        return (int) (Paper::find($this->paper_id)?->students()->count() ?? 0);
    }

    protected function loadAvailableRooms(): void
    {
        if (! $this->day_name || ! $this->start_time || ! $this->end_time || ! $this->paper_id) {
            $this->rooms = collect();

            return;
        }

        $studentCount = $this->getPaperStudentCount();
        $startTime = $this->normalizeTimeValue($this->start_time);
        $endTime = $this->normalizeTimeValue($this->end_time);
        
        $this->rooms = Room::query()
            ->when($studentCount > 0, function ($query) use ($studentCount) {
                $query->whereNotNull('capacity')
                    ->where('capacity', '>=', $studentCount);
            })
            ->whereDoesntHave('paperTimetables', function ($query) use ($startTime, $endTime) {
                $query->where('day_name', $this->day_name)
                    ->whereRaw('start_time < ? AND end_time > ?', [$endTime, $startTime]);

                if ($this->selectedTimetableId) {
                    $query->where('id', '!=', $this->selectedTimetableId);
                }
            })
            ->orderBy('building_name')
            ->orderBy('floor_no')
            ->orderBy('room_number')
            ->get();
    }

    protected function loadFiltersFromTimetable(PaperTimetable $timetable): void
    {
        $this->department_id = $timetable->department_id;
        $this->course_id = $timetable->course_id;
        $this->semester = $timetable->semester;
        $this->paper_id = $timetable->paper_id;

        $this->courses = Courses::where('dept_id', $this->department_id)->get();
        $this->semesters = Paper::where('course_id', $this->course_id)
            ->select('semester')
            ->distinct()
            ->pluck('semester')
            ->toArray();

        $this->papers = Paper::where('course_id', $this->course_id)
            ->where('semester', $this->semester)
            ->get();
    }

    public function openTimetableCalendar($timetableId, $mode = 'view')
    {
        $timetable = PaperTimetable::findOrFail($timetableId);

        $this->loadFiltersFromTimetable($timetable);
        $this->activeSection = 'create';
        $this->calendarMode = $mode === 'edit' ? 'edit' : 'view';
        $this->selectedTimetableId = null;
        $this->showModal = false;
        $this->showCalendar = true;

        $this->loadOccupiedSlots();
    }

    public function showCreateSection()
    {
        $this->hideCalendar();
        $this->activeSection = 'create';
        $this->showModal = false;
    }

    public function showCreatedSection()
    {
        $this->hideCalendar();
        $this->activeSection = 'created';
        $this->showCalendar = false;
        $this->showModal = false;
        $this->calendarMode = 'create';
        $this->selectedTimetableId = null;
    }

    protected function resetSlotForm(): void
    {
        $this->teacher_id = '';
        $this->room_id = '';
        $this->is_lecture = null;
        $this->is_tutorial = null;
        $this->is_practical = null;
        $this->is_coordinator = null;
        $this->selectedTimetableId = null;
    }

    protected function hideCalendar(): void
    {
        $this->showCalendar = false;
        $this->showModal = false;
        $this->calendarMode = 'create';
        $this->selectedTimetableId = null;
        $this->occupiedSlots = [];
    }

    public function editSlot($slotId)
    {
        if ($this->calendarMode !== 'edit') {
            return;
        }

        $slot = PaperTimetable::with(['paper', 'room', 'teacher'])->findOrFail($slotId);

        $this->selectedTimetableId = $slot->id;
        $this->day_name = $slot->day_name;
        $this->start_time = $slot->start_time;
        $this->end_time = $slot->end_time;
        $this->teacher_id = $slot->teacher_id;
        $this->room_id = $slot->room_id;
        $this->is_lecture = $slot->is_lecture;
        $this->is_tutorial = $slot->is_tutorial;
        $this->is_practical = $slot->is_practical;
        $this->is_coordinator = $slot->is_coordinator;

        $this->faculty = Teacher::all();
        $this->loadAvailableRooms();
        $this->modalKey++;

        $this->showModal = true;
    }

    public function updatedPaperId()
    {
        $this->hideCalendar();
        $this->room_id = '';
    }

    public function updatedDepartmentId()
    {
        $this->hideCalendar();
        $this->course_id = '';
        $this->semester = '';
        $this->paper_id = '';

        $this->courses = Courses::where(
            'dept_id',
            $this->department_id
        )->get();

        $this->semesters = [];
        $this->papers = [];
    }

    public function updatedCourseId()
    {
        $this->hideCalendar();
        $this->semester = '';
        $this->paper_id = '';

        $this->semesters = Paper::where(
            'course_id',
            $this->course_id
        )
        ->select('semester')
        ->distinct()
        ->pluck('semester')
        ->toArray();

        $this->papers = [];
    }
 

    public function openSlotModal($day,$slot)
{
    if ($this->calendarMode === 'view') {
        return;
    }

    $this->day_name = $day;
    $this->selectedTimetableId = null;
    $this->resetSlotForm();

    [$start,$end] = explode('-', $slot);

    $this->faculty = Teacher::all();
    $this->start_time = $start;
    $this->end_time = $end;

    $this->loadAvailableRooms();
    $this->modalKey++;

    $this->showModal = true;
}

    protected function formatSlotKey($startTime, $endTime)
    {
        return substr((string) $startTime, 0, 5) . '-' . substr((string) $endTime, 0, 5);
    }

    public function loadOccupiedSlots()
    {
        if (! $this->paper_id) {
            $this->occupiedSlots = [];

            return;
        }

        $this->occupiedSlots = PaperTimetable::with(['paper', 'room'])
            ->where('department_id', $this->department_id)
            ->where('course_id', $this->course_id)
            ->where('semester', $this->semester)
            ->where('paper_id', $this->paper_id)
            ->get()
            ->mapWithKeys(function ($slot) {
                $roomLabel = trim(collect([
                    $slot->room?->building_name,
                    $slot->room?->floor_no,
                    $slot->room?->room_number,
                ])->filter()->implode(' - '));

                return [
                    $slot->day_name . '|' . $this->formatSlotKey($slot->start_time, $slot->end_time) => [
                        'id' => $slot->id,
                        'day_name' => $slot->day_name,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'paper_name' => $slot->paper?->name,
                        'room_label' => $roomLabel,
                    ],
                ];
            })
            ->toArray();
    }

public function closeModal()
{
    $this->resetSlotForm();
    $this->showModal = false;
    $this->modalKey++;
}
    public function updatedSemester()
    {
            $this->hideCalendar();
        $this->paper_id = '';

        $this->papers = Paper::where(
            'course_id',
            $this->course_id
        )
        ->where(
            'semester',
            $this->semester
        )
        ->get();
    }

    public function loadCalendar()
    {
        $this->validate([
            'department_id' => 'required',
            'course_id'     => 'required',
            'semester'      => 'required',
            'paper_id'      => 'required',
        ]);

        $this->showCalendar = true;
        $this->activeSection = 'create';
    $this->calendarMode = 'create';
    $this->selectedTimetableId = null;
        $this->loadOccupiedSlots();

        $this->dispatch(
            'load-calendar',
            paperId: $this->paper_id
        );
    }
public function save()
{
   $this->validate([
            'department_id' => 'required',
            'course_id'     => 'required',
            'semester'      => 'required',
            'paper_id'      => 'required',
            'teacher_id'    => 'required',
            'room_id'       => 'required',
            'day_name'      => 'required',
            'start_time'    => 'required',
            'end_time'      => 'required', 
        ]);

        $this->loadAvailableRooms();

        if ($this->rooms->isEmpty() || ! $this->rooms->firstWhere('id', $this->room_id)) {
            throw ValidationException::withMessages([
                'room_id' => 'The selected room is not available for this slot or does not have enough capacity.',
            ]);
        }

        $payload = [
            'department_id' => $this->department_id,
            'course_id'     => $this->course_id,
            'semester'      => $this->semester,
            'paper_id'      => $this->paper_id,
            'teacher_id'    => $this->teacher_id,
            'room_id'       => $this->room_id,
            'day_name'      => $this->day_name,
            'start_time'    => $this->start_time,
            'end_time'      => $this->end_time,
            'is_lecture'    => $this->is_lecture,
            'is_tutorial'   => $this->is_tutorial,
            'is_practical'  => $this->is_practical,
            'is_coordinator'=> $this->is_coordinator,
        ];

        if ($this->selectedTimetableId) {
            PaperTimetable::whereKey($this->selectedTimetableId)->update($payload);
            session()->flash('success', 'Timetable updated successfully.');
        } else {
            PaperTimetable::create($payload);
            session()->flash('success', 'Timetable created successfully.');
        }

        $this->loadOccupiedSlots();

        $this->dispatch(
            'timetable-updated'
        );
        $this->reset([
            'teacher_id',
            'room_id',
            'is_lecture',
            'is_tutorial',
            'is_practical',
            'is_coordinator',
            'day_name',
            'start_time',
            'end_time',
        ]);
        $this->selectedTimetableId = null;
        $this->closeModal(); 
}
    public function render()
    {
        return view(
            'livewire.admin.timetable-manager',
            [
                'departments' => Departments::all(),
                'timetables' => PaperTimetable::with(['paper', 'room', 'teacher'])
                    ->latest()
                    ->get()
            ]
        );
    }
}