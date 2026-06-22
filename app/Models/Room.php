<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'building_name',
        'floor_no',
        'room_number',
        'is_lab',
        'capacity',
        'remarks'
    ];

    public function paperTimetables()
    {
        return $this->hasMany(PaperTimetable::class, 'room_id');
    }
}
