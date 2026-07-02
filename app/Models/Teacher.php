<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
class Teacher extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    
    protected $guard = 'teacher';
    protected $table = 'faculty_users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', 1);
        });
    }
     public function details(){
        return $this->hasMany(FacultyDetail::class,'faculty_user_id');
    }

    public function department(){
        return $this->belongsTo(Departments::class,'department_id');
    }

    public function hasRole($role_name){
        $role = Role::where("name",$role_name)->first();
        if(!$role){
            return false;
        }
        return  RoleAssignment::where('auth_type','teacher')->where('auth_id',$this->id)->where("role_id",$role->id)->first() ?? false;
    }
}
