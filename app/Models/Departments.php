<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $guarded = [];


public function courses()
{
    return $this->hasMany(\App\Models\Courses::class, 'dept_id', 'id');
}


}
