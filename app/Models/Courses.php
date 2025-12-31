<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    protected $guarded = [];

    public function department()
{
    return $this->belongsTo(\App\Models\Departments::class, 'dept_id', 'id');
}

}
