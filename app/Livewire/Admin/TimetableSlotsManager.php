<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\TimetableSlot;

class TimetableSlotsManager extends Component
{
    public $start_time = '';
    public $end_time = '';

    protected $rules = [
        'start_time' => 'required',
        'end_time' => 'required',
    ];

    public function save()
    {
        $this->validate();

        // Convert times for comparisons
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);

        if ($start >= $end) {
            session()->flash('error', 'Start time must be before end time.');
            return;
        }

        $startTimeFormatted = date('H:i:s', $start);
        $endTimeFormatted = date('H:i:s', $end);

        // Check duplicates
        $exists = TimetableSlot::where('start_time', $startTimeFormatted)
            ->where('end_time', $endTimeFormatted)
            ->exists();

        if ($exists) {
            session()->flash('error', 'This time slot already exists.');
            return;
        }

        TimetableSlot::create([
            'start_time' => $startTimeFormatted,
            'end_time' => $endTimeFormatted,
        ]);

        $this->reset(['start_time', 'end_time']);
        session()->flash('success', 'Timetable slot added successfully.');
    }

    public function deleteSlot($slotId)
    {
        $slot = TimetableSlot::findOrFail($slotId);
        $slot->delete();

        session()->flash('success', 'Timetable slot deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.timetable-slots-manager', [
            'timeSlots' => TimetableSlot::orderBy('start_time')->get()
        ]);
    }
}
