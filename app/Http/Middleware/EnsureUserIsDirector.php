<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsDirector
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'director') {
            return $next($request);
        }

        return redirect()->route('home')->with('error', 'Chỉ giám đốc được phép truy cập trang này.');
    }
}