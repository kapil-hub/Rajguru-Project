<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaMark extends Model
{
    protected $fillable = [
        'student_id',
        'paper_master_id',
        'course_id',
        'semester_id',
        'section',
        'tute_ca',
        'class_test',
        'assignment',
        'attendance',
        'total',
        'total_tute_marks',
        'grand_total',
        'created_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
