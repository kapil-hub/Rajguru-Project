<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AparSubmission extends Model
{
    protected $fillable = [
        'faculty_user_id',
        'academic_year',
        'status',
        'form_data',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'hod_teaching_grade',
        'hod_b2_grade',
        'hod_overall_grade',
    ];

    protected function casts(): array
    {
        return [
            'form_data' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function faculty()
    {
        return $this->belongsTo(Teacher::class, 'faculty_user_id')
            ->withoutGlobalScope('active')
            ->withTrashed();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
