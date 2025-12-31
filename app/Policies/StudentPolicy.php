<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Student;
use App\Models\User; // or Student user model

class StudentPolicy
{
    /**
     * Admin can view any student
     * Student can view only self
     */
    public function view($user, Student $student)
    {
        // Admin guard
        if (auth()->guard('admin')->check()) {
            return true;
        }

        // Student guard → only self
        if (auth()->guard('student')->check()) {
            return $user->id === $student->id;
        }

        return false;
    }

    public function update($user, Student $student)
    {
        // Admin can update any student
        if (auth()->guard('admin')->check()) {
            return true;
        }

        // Student can update only self
        if (auth()->guard('student')->check()) {
            return $user->id === $student->id;
        }

        return false;
    }
}
