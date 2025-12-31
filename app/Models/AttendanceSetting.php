<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'attendance_type',
        'academic_session',
        'semester_type',
        'start_month',
        'end_month',
        'status'
    ];

    protected $casts = [
        'start_month' => 'integer',
        'end_month'   => 'integer',
    ];
}
