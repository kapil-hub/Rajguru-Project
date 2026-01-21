<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Subscription;
class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        // Students are always free
        if (auth()->guard('student')->check()) {
            return $next($request);
        }

        if ($request->routeIs([
            'home',
            'logout',
            'login.submit',
            'subscription.expired',
            'subscription.plans',
            'subscription.subscribe',
        ])) {
            return $next($request);
        }

        if (auth()->guard('admin')->check()) {
            $guard = 'admin';
            $user  = auth()->guard('admin')->user();
        } elseif (auth()->guard('teacher')->check()) {
            $guard = 'teacher';
            $user  = auth()->guard('teacher')->user();
        } else {
            // Guest user
            return $next($request);
        }
        // dd("fff");
        $subscription = Subscription::where([
            'user_id' => $user->id,
            'guard'   => $guard,
        ])->first();

        if (!$subscription || !$subscription->isActive()) {
            return redirect('/subscription-expired');
        }

        return $next($request);
    }
}
