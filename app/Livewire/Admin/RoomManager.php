<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Room;

class RoomManager extends Component
{
    use WithPagination;

    // ── List / search ────────────────────────────────────────────────
    public string $search = '';
    public string $filterType = ''; // '' | 'lab' | 'classroom'

    // ── Modal state ──────────────────────────────────────────────────
    public bool $showModal  = false;
    public bool $showDelete = false;
    public ?int $editId     = null;
    public ?int $deleteId   = null;

    // ── Form fields ──────────────────────────────────────────────────
    public string $building_name = '';
    public string $floor_no      = '';
    public string $room_number   = '';
    public bool   $is_lab        = false;
    public string $capacity      = '';
    public string $remarks       = '';

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'building_name' => 'required|string|max:100',
            'floor_no'      => 'required|string|max:20',
            'room_number'   => 'required|string|max:20',
            'is_lab'        => 'boolean',
            'capacity'      => 'nullable|integer|min:1|max:9999',
            'remarks'       => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'building_name.required' => 'Building name is required.',
        'floor_no.required'      => 'Floor number is required.',
        'room_number.required'   => 'Room number is required.',
        'capacity.integer'       => 'Capacity must be a whole number.',
        'capacity.min'           => 'Capacity must be at least 1.',
    ];

    // Reset pagination when search/filter changes
    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }

    // ── Modal helpers ─────────────────────────────────────────────────

    public function openModal(): void
    {
        $this->resetForm();
        $this->editId    = null;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $room = Room::findOrFail($id);

        $this->editId        = $id;
        $this->building_name = $room->building_name;
        $this->floor_no      = $room->floor_no;
        $this->room_number   = $room->room_number;
        $this->is_lab        = (bool) $room->is_lab;
        $this->capacity      = (string) ($room->capacity ?? '');
        $this->remarks       = (string) ($room->remarks ?? '');

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId   = $id;
        $this->showDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId   = null;
        $this->showDelete = false;
    }

    // ── CRUD ──────────────────────────────────────────────────────────

    public function save(): void
    {
        $data = $this->validate();

        // Normalise capacity to null when empty
        $data['capacity'] = $data['capacity'] !== '' && $data['capacity'] !== null
            ? (int) $data['capacity']
            : null;

        if ($this->editId) {
            Room::whereKey($this->editId)->update($data);
            session()->flash('success', 'Room updated successfully.');
        } else {
            Room::create($data);
            session()->flash('success', 'Room added successfully.');
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $room = Room::find($this->deleteId);

        if (! $room) {
            session()->flash('error', 'Room not found.');
            $this->cancelDelete();
            return;
        }

        // Guard: do not delete rooms that are referenced by timetables
        if ($room->paperTimetables()->exists()) {
            session()->flash('error', 'Cannot delete this room — it is assigned to one or more timetable entries.');
            $this->cancelDelete();
            return;
        }

        $room->delete();
        session()->flash('success', 'Room deleted successfully.');
        $this->cancelDelete();
    }

    // ── Helpers ───────────────────────────────────────────────────────

    protected function resetForm(): void
    {
        $this->building_name = '';
        $this->floor_no      = '';
        $this->room_number   = '';
        $this->is_lab        = false;
        $this->capacity      = '';
        $this->remarks       = '';
        $this->resetErrorBag();
    }

    // ── Render ────────────────────────────────────────────────────────

    public function render()
    {
        $rooms = Room::query()
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('building_name', 'like', '%' . $this->search . '%')
                          ->orWhere('room_number',   'like', '%' . $this->search . '%')
                          ->orWhere('floor_no',      'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterType === 'lab',       fn($q) => $q->where('is_lab', true))
            ->when($this->filterType === 'classroom',  fn($q) => $q->where('is_lab', false))
            ->orderBy('building_name')
            ->orderBy('floor_no')
            ->orderBy('room_number')
            ->paginate(12);

        $stats = [
            'total'      => Room::count(),
            'labs'       => Room::where('is_lab', true)->count(),
            'classrooms' => Room::where('is_lab', false)->count(),
            'capacity'   => Room::whereNotNull('capacity')->sum('capacity'),
        ];

        return view('livewire.admin.room-manager', compact('rooms', 'stats'));
    }
}
