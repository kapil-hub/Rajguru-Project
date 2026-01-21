<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'guard',
        'trial_ends_at',
        'subscription_ends_at'
    ];

    protected $dates = [
        'trial_ends_at',
        'subscription_ends_at'
    ];

    public function isActive(): bool
    {
        if ($this->trial_ends_at && now()->lte($this->trial_ends_at)) {
            return true;
        }

        if ($this->subscription_ends_at && now()->lte($this->subscription_ends_at)) {
            return true;
        }

        return false;
    }
}
