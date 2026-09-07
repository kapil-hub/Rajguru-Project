<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'department_ids' => 'array',
        'course_ids' => 'array',
        'paper_ids' => 'array',
        'target_all_active_students' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function matchesStudent(Student $student): bool
    {
        if ($this->target_all_active_students) {
            return true;
        }

        $academic = $student->academic;
        $studentPaperIds = $student->papers->pluck('paper_master_id')->map(fn ($id) => (int) $id);

        return ($academic && in_array((int) $academic->department_id, $this->department_ids ?? [], true))
            || ($academic && in_array((int) $academic->course_id, $this->course_ids ?? [], true))
            || $studentPaperIds->intersect($this->paper_ids ?? [])->isNotEmpty();
    }
}
