<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClassAssignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'course_id',
        'semester_id',
        'section',
        'paper_master_id',
        'is_lecture',
        'is_tute',
        'is_practical',
        'is_coordinator',
        'academic_session',
        'is_active'
    ];

    // Relations
    public function teacher() {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function course() {
        return $this->belongsTo(\App\Models\Courses::class, 'course_id');
    }

    public function semester() {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    // This is for the Paper Master table

    public function paperMaster() {
        return $this->belongsTo(\App\Models\Paper::class, 'paper_master_id');
    }

    // Returns pending months (current + past incomplete)
    public function pendingMonths() {
        $currentMonth = date('n');
        $currentYear = date('Y');

        $months = [];

        for ($m = 1; $m <= $currentMonth; $m++) {
            $exists = \App\Models\StudentAttendance::where('teacher_id', $this->teacher_id)
                ->where('course_id', $this->course_id)
                ->where('semester_id', $this->semester_id)
                ->where('section', $this->section)
                ->where('paper_master_id', $this->paper_master_id)
                ->where('month', $m)
                ->where('year', $currentYear)
                ->exists();

            if (!$exists) {
                $months[] = ['month' => $m, 'year' => $currentYear];
            }
        }

        return $months;
    }
}
