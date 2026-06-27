<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAcademicHistory extends Model
{
    protected $table = 'student_academic_history';
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_user_id');
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
