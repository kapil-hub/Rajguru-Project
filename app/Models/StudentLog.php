<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLog extends Model
{
    protected $fillable = [
        'paper_master_id',
        'student_user_id',
        'log_count',
        'remark',
        'created_by_auth_type',
        'created_by_id'
    ];
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_user_id');
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_master_id');
    }

}
