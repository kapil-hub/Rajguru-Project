<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable
{
    use Notifiable;

    protected $guard = 'teacher';
    protected $table = 'faculty_users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

     public function details(){
        return $this->hasMany(FacultyDetail::class,'faculty_user_id');
    }

    public function department(){
        return $this->belongsTo(Departments::class,'department_id');
    }
}
