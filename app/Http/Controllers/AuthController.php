<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        Auth::guard('admin')->logout();
        Auth::guard('teacher')->logout();
        Auth::guard('student')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return view('pages.auth.login');
    }

   public function login(Request $request)
    {
        // Validate input first
        $request->validate([
            'role' => 'required|in:student,teacher,admin',
            'login' => 'required',
            'password' => 'required',
        ]);

        $user = null;

        if ($request->role === 'student') {
            $user = Student::where('control_number', $request->login)
                ->orWhere('email', $request->login)
                ->orWhere('mobile', $request->login)
                ->first();

            $guard = 'student';
        }elseif($request->role === 'admin'){
            $user = User::where('email', $request->login)
                ->first();

            $guard = 'admin';
        } else {
            $user = Teacher::where('email', $request->login)
                ->orWhere('mobile', $request->login)
                ->first();

            $guard = 'teacher';
        }

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->route('login')->withErrors(['login' => 'Invalid credentials'])->withInput();
        }

        Auth::shouldUse($guard);
        Auth::guard($guard)->login($user);

        // 🔑 store active guard
        session(['active_guard' => $guard]);

        return redirect()->route('dashboard');

        // Redirect to dashboard (can separate for student/teacher if needed)
        return redirect()->route('dashboard');
    }


    public function logout(Request $request)
    {
        Auth::forgetGuards();

        Auth::guard('admin')->logout();
        Auth::guard('teacher')->logout();
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showChangePassword()
    {
        return view('pages.auth.change-password');
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        // detect logged-in guard
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $guard = 'admin';
        } elseif (Auth::guard('teacher')->check()) {
            $user = Auth::guard('teacher')->user();
            $guard = 'teacher';
        } else {
            $user = Auth::guard('student')->user();
            $guard = 'student';
        }

        // check old password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // logout after change (SECURITY)
        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Password changed successfully. Please login again.');
    }


}
