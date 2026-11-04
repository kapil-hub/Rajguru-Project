<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function students(){
      return $this->hasMany(StudentPaper::class,'paper_master_id');
    }

    public function registrations()
{
    return $this->hasMany(StudentSubjectRegistrationForm::class, 'paper_master_id');
}

    public function theoryMarks()
    {
        return $this->hasMany(StudentTheoryMark::class, 'paper_id');
    }

    public function scopeEligibleForTheoryMarksByCoordinator(Builder $query, int $teacherId): Builder
    {
        return $query
            ->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereIn('paper_type', ['SEC', 'VAC', 'AEC'])
                        ->where('number_of_lectures', 1)
                        ->where('number_of_tutorials', 0)
                        ->where('number_of_practicals', 1);
                })->orWhere(function (Builder $query) {
                    $query->where('paper_type', 'AEC')
                        ->where('number_of_lectures', 2)
                        ->where('number_of_tutorials', 0)
                        ->where('number_of_practicals', 0);
                });
            })
            ->whereExists(function ($query) use ($teacherId) {
                $query->selectRaw('1')
                    ->from('paper_timetables')
                    ->whereColumn('paper_timetables.paper_id', 'paper_master.id')
                    ->whereColumn('paper_timetables.course_id', 'paper_master.course_id')
                    ->whereColumn('paper_timetables.semester', 'paper_master.semester')
                    ->where('paper_timetables.teacher_id', $teacherId)
                    ->where('paper_timetables.is_coordinator', true);
            });
    }
}
