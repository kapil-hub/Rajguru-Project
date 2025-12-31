<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $guarded = [];

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