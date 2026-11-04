<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTheoryMark extends Model
{
    protected $fillable = [
        'student_id',
        'paper_id',
        'teacher_id',
        'theory_marks',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
