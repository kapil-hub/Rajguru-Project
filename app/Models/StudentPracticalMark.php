<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPracticalMark extends Model
{
    protected $fillable = [
        'student_id',
        'paper_id',
        'teacher_id',
        'continuous_assessment',
        'end_sem_practical',
        'viva_voce',
        'total_marks',
    ];
    public function student()
{
    return $this->belongsTo(Student::class);
}

      public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
}
