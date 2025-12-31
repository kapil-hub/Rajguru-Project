<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDailyAttendance extends Model
{
    protected $fillable = [
        'teacher_id',
        'student_id',
        'paper_master_id',
        'course_id',
        'semester_id',
        'section',
        'attendance_date',
        'lecture',
        'tute',
        'practical'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'lecture' => 'boolean',
        'tute' => 'boolean',
        'practical' => 'boolean',
    ];

    public function paper(){
        return $this->belongsTo('App\Models\Paper','paper_master_id');
    }

    public function student(){
        return $this->belongsTo('App\Models\Student','student_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
