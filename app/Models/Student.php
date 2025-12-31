<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $guard = 'student';
    protected $table = 'student_users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function academic()
    {
        return $this->hasOne(StudentAcademic::class,'student_user_id');
    }


    public function enrolDetails()
    {
        return $this->hasOne(StudentEnrolDetail::class,'student_user_id');
    }

    public function papers()
    {
        return $this->hasMany(StudentPaper::class,'student_user_id');
    }

    public function enrollments()
    {
        return $this->hasMany(\App\Models\StudentEnrolDetail::class, 'student_user_id');
    }
    public function dailyAttendances()
    {
        return $this->hasMany(\App\Models\StudentDailyAttendance::class, 'student_id', 'id');
    }
}
