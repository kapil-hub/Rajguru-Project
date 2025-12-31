<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAcademic extends Model
{
    use HasFactory;

    protected $table = 'student_academic';

    protected $guarded = [];

    /** Student belongs to user */
    public function student()
    {
        return $this->belongsTo(StudentUser::class, 'student_user_id');
    }

    /** Department */
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    /** Course */
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
}
