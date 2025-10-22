<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsDirector
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role !== 'director') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này'], 403);
        }
        return $next($request);
    }
}