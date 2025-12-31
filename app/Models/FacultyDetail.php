<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyDetail extends Model
{
    protected $table = 'faculty_details';
    protected $fillable = ['faculty_user_id','department_id','course_id','paper_master_id'];

    public function course(){
        return $this->belongsTo(\App\Models\Courses::class,'course_id');
    }

    public function paperMaster(){
        return $this->belongsTo(\App\Models\Paper::class,'paper_master_id');
    }

    public function faculty(){
        return $this->belongsTo(Teacher::class,'faculty_user_id');
    }
}
