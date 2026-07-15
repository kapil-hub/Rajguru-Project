<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LateHeldRequest extends Model
{
    protected $fillable = [
        'teacher_id',
        'department_id',
        'paper_timetable_id',
        'held_date',
        'status',
        'reason',
        'tic_remark',
        'action_by',
        'action_at',
        'read_at',
    ];

    protected $casts = [
        'held_date' => 'date',
        'action_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function timetable()
    {
        return $this->belongsTo(PaperTimetable::class, 'paper_timetable_id');
    }

    public function actionBy()
    {
        return $this->belongsTo(Teacher::class, 'action_by');
    }
}
