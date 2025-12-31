<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Paper;

class StudentPaper extends Model
{
    //
    protected $guarded = [];

    public function paper(){
        return $this->belongsTo(Paper::class,'paper_master_id');
    }
    
}
