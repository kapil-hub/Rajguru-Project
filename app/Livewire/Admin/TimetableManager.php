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
use App\Models\TimetableSlot;

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

public $timeSlots = [];
public $availableBatches = [];
public $selectedBatches = [];
public $selected_filter_batches = [];
  protected $listeners = [
    'open-slot-modal'
];

    public function mount()
    {
        $this->timeSlots = TimetableSlot::orderBy('start_time')
            ->get()
            ->map(fn($s) => $s->formatted_slot)
            ->toArray();

        if (auth('teacher')->check()) {
            $teacher = auth('teacher')->user();
            if ($teacher->hasRole('Timetable Controller')) {
                $this->department_id = $teacher->department_id;
                $this->courses = Courses::where('dept_id', $this->department_id)->orWhere('id', 15)->get();
            } else {
                abort(403, 'Unauthorized.');
            }
        }
    }

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
        $this->selected_filter_batches = explode(',', $timetable->batches);

        $this->courses = Courses::where('dept_id', $this->department_id)->orWhere('id', 15)->get();
        $this->semesters = Paper::where('course_id', $this->course_id)->where('status', 'Active')
            ->select('semester')
            ->distinct()
            ->pluck('semester')
            ->toArray();

        $this->papers = Paper::where('course_id', $this->course_id)
            ->where('semester', $this->semester)
            ->where('status', 'Active')
            ->get();
    }

    public function openTimetableCalendar($timetableId, $mode = 'view')
    {
        $timetable = PaperTimetable::findOrFail($timetableId);

        if (auth('teacher')->check() && $mode === 'edit') {
            if (is_null($timetable->created_by_type) || $timetable->created_by_type === 'admin') {
                session()->flash('error', 'You cannot edit timetables created by an admin.');
                return;
            }
        }

        $this->loadFiltersFromTimetable($timetable);
        $this->activeSection = 'create';
        $this->calendarMode = $mode === 'edit' ? 'edit' : 'view';
        $this->selectedTimetableId = null;
        $this->showModal = false;
        $this->showCalendar = true;
        $this->loadAvailableBatches();
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

        if (auth('teacher')->check()) {
            if (is_null($slot->created_by_type) || $slot->created_by_type === 'admin') {
                session()->flash('error', 'You cannot edit timetables created by an admin.');
                return;
            }
        }

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
        $this->paper_id = $slot->paper_id;
        $this->loadAvailableBatches();
        $this->selectedBatches = $slot->batches ? explode(',', $slot->batches) : [];

        $this->faculty = Teacher::all();
        $this->loadAvailableRooms();
        $this->modalKey++;

        $this->showModal = true;
    }

    public function loadAvailableBatches()
    {
        if ($this->paper_id) {
            $this->availableBatches = \App\Models\StudentPaper::where('paper_master_id', $this->paper_id)
                ->whereNotNull('batch')
                ->where('batch', '!=', '')
                ->distinct()
                ->pluck('batch')
                ->sort()
                ->toArray();
        } else {
            $this->availableBatches = [];
        }
    }

    public function updatedPaperId()
    {
        $this->hideCalendar();
        $this->room_id = '';
        $this->loadAvailableBatches();
        $this->selectedBatches = [];
        $this->selected_filter_batches = [];
    }

    public function updatedDepartmentId()
    {
        $this->hideCalendar();
        $this->course_id = '';
        $this->semester = '';
        $this->paper_id = '';
        $this->selected_filter_batches = [];

        $this->courses = Courses::where(
            'dept_id',
            $this->department_id
        )->orWhere('id',15)->get();

        $this->semesters = [];
        $this->papers = [];
    }
    public function updateSelectedFilterBatches() {
       $this->hideCalendar();
    }
    public function updatedCourseId()
    {
        $this->hideCalendar();
        $this->semester = '';
        $this->paper_id = '';
        $this->selected_filter_batches = [];

        $this->semesters = Paper::where(
            'course_id',
            $this->course_id
        )
        ->where('status', 'Active')
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
    $this->selectedBatches = [];
    $this->loadAvailableBatches();

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

        $query = PaperTimetable::with(['paper', 'room'])
            ->where('department_id', $this->department_id)
            ->where('course_id', $this->course_id)
            ->where('semester', $this->semester)
            ->where('paper_id', $this->paper_id);

        if (count($this->selected_filter_batches) > 0) {
            $query->where(function ($q) {
                $q->whereNull('batches')
                  ->orWhere('batches', '');
                foreach ($this->selected_filter_batches as $batch) {
                    $q->orWhere('batches', 'like', '%' . $batch . '%');
                }
            });
        }

        $this->occupiedSlots = $query->get()
            ->groupBy(function ($slot) {
                return $slot->day_name . '|' . $this->formatSlotKey($slot->start_time, $slot->end_time);
            })
            ->map(function ($group) {
                return $group->map(function ($slot) {
                    $roomLabel = trim(collect([
                        $slot->room?->building_name,
                        $slot->room?->floor_no,
                        $slot->room?->room_number,
                    ])->filter()->implode(' - '));

                    return [
                        'id' => $slot->id,
                        'day_name' => $slot->day_name,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'paper_name' => $slot->paper?->name,
                        'room_label' => $roomLabel,
                        'batches' => $slot->batches,
                    ];
                })->toArray();
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
        $this->selected_filter_batches = [];

        $this->papers = Paper::where(
            'course_id',
            $this->course_id
        )
        ->where('status', 'Active')
        ->where(
            'semester',
            $this->semester
        )
        ->get();
    }

    public function loadCalendar()
    {
        $rules = [
            'department_id' => 'required',
            'course_id'     => 'required',
            'semester'      => 'required',
            'paper_id'      => 'required',
        ];

        if (count($this->availableBatches) > 0) {
            $rules['selected_filter_batches'] = 'required|array|min:1';
        }

        $this->validate($rules, [
            'selected_filter_batches.required' => 'Please select at least one batch to load the timetable.',
            'selected_filter_batches.min' => 'Please select at least one batch to load the timetable.',
        ]);

        $this->showCalendar = true;
        $this->activeSection = 'create';
    $this->calendarMode = 'create';
        $this->selectedTimetableId = null;
        $this->loadAvailableBatches();
        $this->loadOccupiedSlots();

        $this->dispatch(
            'load-calendar',
            paperId: $this->paper_id
        );
    }
public function save()
{
    // dd($this->selectedBatches);
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
            'batches'       => $this->selectedTimetableId 
                               ? PaperTimetable::findOrFail($this->selectedTimetableId)->batches 
                               : (count($this->selected_filter_batches) > 0 ? implode(',', $this->selected_filter_batches) : null),
        ];

        if ($this->selectedTimetableId) {
            $slot = PaperTimetable::findOrFail($this->selectedTimetableId);
            if (auth('teacher')->check()) {
                if (is_null($slot->created_by_type) || $slot->created_by_type === 'admin') {
                    session()->flash('error', 'You cannot edit timetables created by an admin.');
                    return;
                }
            }
            PaperTimetable::whereKey($this->selectedTimetableId)->update($payload);
            session()->flash('success', 'Timetable updated successfully.');
        } else {
            if (auth('admin')->check()) {
                $payload['created_by_id'] = auth('admin')->id();
                $payload['created_by_type'] = 'admin';
            } elseif (auth('teacher')->check()) {
                $payload['created_by_id'] = auth('teacher')->id();
                $payload['created_by_type'] = 'teacher';
            }
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

public function deleteTimetable($id){
    $timetable = PaperTimetable::findOrFail($id);
    if (auth('teacher')->check()) {
        if (is_null($timetable->created_by_type) || $timetable->created_by_type === 'admin') {
            session()->flash('error', 'You cannot delete timetables created by an admin.');
            return;
        }
        $timetable->delete();
        session()->flash('success', 'Timetable deleted successfully.');
    }

    if(auth('admin')->check()) {
        $timetable->delete();
        session()->flash('success', 'Timetable deleted successfully.');
    }   
}

    public function render()
    {
        $departmentsQuery = Departments::query();
        $timetablesQuery = PaperTimetable::with(['paper', 'room', 'teacher'])->latest();

        if (auth('teacher')->check()) {
            $deptId = auth('teacher')->user()->department_id;
            $departmentsQuery->where('id', $deptId);
            $timetablesQuery->where('department_id', $deptId);
        }

        return view(
            'livewire.admin.timetable-manager',
            [
                'departments' => $departmentsQuery->get(),
                'timetables' => $timetablesQuery->get()
            ]
        );
    }
}