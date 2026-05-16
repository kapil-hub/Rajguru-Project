<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleAssignment extends Model
{
    protected $fillable = [
        'auth_type',
        'auth_id',
        'role_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}