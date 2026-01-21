<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        return view('subscription.plans');
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:basic,standard,premium',
        ]);

        // detect guard
        if (Auth::guard('admin')->check()) {
            $guard = 'admin';
            $user  = Auth::guard('admin')->user();
        } elseif (Auth::guard('teacher')->check()) {
            $guard = 'teacher';
            $user  = Auth::guard('teacher')->user();
        } else {
            abort(403);
        }

        // fetch or create subscription row
        $subscription = Subscription::firstOrCreate(
            [
                'user_id' => $user->id,
                'guard'   => $guard,
            ],
            [
                'trial_ends_at' => null,
            ]
        );

        // TEST MODE: activate paid subscription (1 year)
        $subscription->update([
            'subscription_ends_at' => Carbon::now()->addYear(),
            'is_active'            => 1,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Subscription activated successfully (TEST MODE)');
    }
}
