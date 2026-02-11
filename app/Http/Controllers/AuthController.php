<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

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

        Auth::guard($guard)->login($user);

        // Redirect to dashboard (can separate for student/teacher if needed)
        return redirect()->route('dashboard');
    }


    public function logout(Request $request)
    {

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }


        if (Auth::guard('student')->check()) {
            Auth::guard('student')->logout();
        }

        if (Auth::guard('teacher')->check()) {
            Auth::guard('teacher')->logout();
        }

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


    public function showForgot()
{
    return view('pages.auth.forgot-password');
}

public function sendOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'role'  => 'required|in:student,teacher,admin'
    ]);

    // check user exists
    $user = match ($request->role) {
        'student' => Student::where('email', $request->email)->first(),
        'teacher' => Teacher::where('email', $request->email)->first(),
        'admin'   => User::where('email', $request->email)->first(),
    };

    if (!$user) {
        return back()->withErrors(['email' => 'User not found']);
    }

    $otp = rand(100000, 999999);

    DB::table('password_otps')->updateOrInsert(
        ['email' => $request->email],
        [
            'role' => $request->role,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    Mail::send('emails.password-otp', [
        'otp' => $otp,
        'name' => $user->name ?? 'User'
    ], function ($mail) use ($request) {
        $mail->to($request->email)
             ->subject('🔐 Password Reset OTP');
    });

    session([
        'reset_email' => $request->email,
        'reset_role' => $request->role,
    ]);

    return redirect('/verify-otp')->with('success', 'OTP sent to your email');
}

    public function verifyOtp(Request $request)
{
    $request->validate(['otp' => 'required']);

    $record = DB::table('password_otps')
        ->where('email', session('reset_email'))
        ->where('otp', $request->otp)
        ->where('expires_at', '>', now())
        ->first();

    if (!$record) {
        return back()->withErrors(['otp' => 'Invalid or expired OTP']);
    }

    return redirect('/reset-password');
}


public function resetPassword(Request $request)
{
    $request->validate([
        'password' => 'required|min:8|confirmed'
    ]);

    $email = session('reset_email');
    $role = session('reset_role');

    $user = match ($role) {
        'student' => Student::where('email', $email)->first(),
        'teacher' => Teacher::where('email', $email)->first(),
        'admin'   => User::where('email', $email)->first(),
    };

    $user->update([
        'password' => Hash::make($request->password)
    ]);

    DB::table('password_otps')->where('email', $email)->delete();
    session()->forget(['reset_email', 'reset_role']);

    return redirect('/')->with('success', 'Password reset successfully');
}

public function showOtpForm()
{
    if (!session('reset_email')) {
        return redirect('/forgot-password');
    }

    return view('pages.auth.verify-otp');
}

public function showResetPassword()
{
    if (!session('reset_email')) {
        return redirect('/forgot-password');
    }

    return view('pages.auth.reset-password');
}


}
