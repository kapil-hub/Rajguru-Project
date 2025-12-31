<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    protected $table =  'paper_master';
    protected $guarded = [];

    public function department(){
       return $this->hasOne('App\Models\Departments','id','dept_id');
    }

    public function course(){
       return $this->hasOne('App\Models\Courses','id','course_id');
    }
}
