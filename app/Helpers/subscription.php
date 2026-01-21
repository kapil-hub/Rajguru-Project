<?php
use App\Models\Subscription;

function subscriptionActive($user, string $guard): bool
{
    $subscription = Subscription::where([
        'user_id' => $user->id,
        'guard'   => $guard
    ])->first();

    if (!$subscription) {
        return false;
    }

    return $subscription->isActive();
}
