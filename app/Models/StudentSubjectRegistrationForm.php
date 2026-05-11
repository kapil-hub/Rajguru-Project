<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubjectRegistrationForm extends Model
{
    use HasFactory;

    protected $table = 'student_subject_registration_form';

    protected $fillable = [
        'student_user_id',
        'paper_master_id',
        'semester',
        'academic_year',
        'is_backlog',
        'is_approved',
        'approved_by_id',
        'approved_by_type',
        'approved_date',
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