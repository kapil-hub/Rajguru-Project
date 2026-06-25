<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    protected $table = 'timetable_slots';

    protected $fillable = [
        'start_time',
        'end_time',
    ];

    /**
     * Get the formatted slot representation (e.g. 09:00-10:00)
     */
    public function getFormattedSlotAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5) . '-' . substr((string) $this->end_time, 0, 5);
    }
}
