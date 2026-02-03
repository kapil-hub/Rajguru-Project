<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationWindow extends Model
{
    protected $fillable = [
        'department_id',
        'course_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function department()
    {
        return $this->belongsTo(Departments::class);
    }

    // 🔗 Course relation
    public function course()
    {
        return $this->belongsTo(Courses::class);
    }
}
