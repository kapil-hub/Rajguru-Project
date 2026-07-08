<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableHeldPool extends Model
{
    protected $table = 'timetable_held_pools';

    protected $fillable = [
        'teacher_id',
        'course_id',
        'semester_id',
        'paper_master_id',
        'section',
        'month',
        'year',
        'lecture_held',
        'tute_held',
        'practical_held',
        'student_ids',
        'marked_slots',
    ];

    protected $casts = [
        'student_ids' => 'array',
        'marked_slots' => 'array',
    ];

    // Relations
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function paperMaster()
    {
        return $this->belongsTo(Paper::class, 'paper_master_id');
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_master_id');
    }
}
