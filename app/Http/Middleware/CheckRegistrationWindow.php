<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationWindow
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $student = auth('student')->user();
        if (!is_registration_open()) {
             return redirect()->route('students.show',$student->id)
                ->with('error', 'Registration is currently closed');
        }

        return $next($request);
    }
}
