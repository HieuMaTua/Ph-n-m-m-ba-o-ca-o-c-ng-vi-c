<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class AuthController extends Controller
{
    //hiển thị form đăng nhập
    public function showLogin() {
        return view('auth.login');
    }

    //xử lý đăng nhập
    public function login(Request $request) {
        $request -> validate([
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        $LoginInput = $request ->input('login');
        $password = $request -> input('password');

        $field = filter_var($LoginInput,FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if(Auth::attempt([$field => $LoginInput,'password' => $password],$request->remember)) {
            return redirect() -> route('dashboard');
        }
        return back() -> withErrors(['phone' => 'phone hoặc mật khẩu không đúng']);
    }

    //hiển thị form đăng ký
    public function showRegister() {
        return view('auth.register');
    }

    //xử lý form đăng ký
    public function register(Request $request) {
        $request -> validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'in:director,manager,staff',
            'manager_id' => 'nullable|exists:users,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'staff',
            'manager_id' => $request->manager_id
        ]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    //đăng xuất
    public function logout(Request $request) {
        Auth::logout();
        $request -> session()->invalidate();
        $request -> session()->regenerateToken();
        return redirect()->route('login');
    }
}