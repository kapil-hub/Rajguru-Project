<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPapersHistory extends Model
{
    protected $table = 'student_papers_history';
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_user_id');
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_master_id');
    }
}
