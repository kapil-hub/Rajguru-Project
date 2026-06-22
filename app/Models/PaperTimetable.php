<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaperTimetable extends Model
{
     protected $fillable = [
        'department_id',
        'course_id',
        'semester',
        'paper_id',
        'teacher_id',
        'room_id',
        'day_name',
        'start_time',
        'end_time',
        'color',
        'is_lecture',
        'is_tutorial',
        'is_practical',
        'is_coordinator'
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
}
