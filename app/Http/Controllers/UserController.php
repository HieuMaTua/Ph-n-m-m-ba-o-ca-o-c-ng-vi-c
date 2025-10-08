<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Danh sách nhân sự
    public function index()
    {
        $users = User::with('manager')->paginate(10);
        return view('users.index', compact('users'));
    }

    // Form chỉnh sửa
    public function edit(User $user)
    {
        $managers = User::where('role', 'manager')->where('id', '!=', $user->id)->get();
        return view('users.edit', compact('user', 'managers'));
    }

    // Cập nhật dữ liệu
    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:staff,manager,admin',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $user->role = $request->role;
        $user->manager_id = $request->manager_id;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Cập nhật thành công!');
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);
        Auth::user()->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['success' => true]);
    }
}