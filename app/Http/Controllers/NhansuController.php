<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class NhansuController extends Controller
{
    public function index()
    {
        // 🔒 Kiểm tra quyền trước
        if (Auth::user()->role !== 'director') {
            return redirect()->route('home')
                ->with('error', 'Chỉ giám đốc được phép truy cập trang này.');
        }

        $employees = User::with('manager')->paginate(10);
        $managers = User::whereIn('role', ['director', 'manager'])->get();

        $totalEmployees = User::count();
        $directorsCount = User::where('role', 'director')->count();
        $managersCount = User::where('role', 'manager')->count();
        $staffCount = User::where('role', 'staff')->count();

        return view('nhansu', compact(
            'employees',
            'managers',
            'totalEmployees',
            'directorsCount',
            'managersCount',
            'staffCount'
        ));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'director') {
            return redirect()->route('home')
                ->with('error', 'Chỉ giám đốc được phép thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone|regex:/^\d{10,11}$/',
            'email' => 'nullable|email|unique:users,email',
            'role' => 'required|in:director,manager,staff',
            'manager_id' => 'nullable|exists:users,id',
            'password' => 'required|string|min:8',
        ]);

        $employee = new User();
        $employee->name = $validated['name'];
        $employee->phone = $validated['phone'];
        $employee->email = $validated['email'];
        $employee->role = $validated['role'];
        $employee->manager_id = $validated['manager_id'];
        $employee->password = Hash::make($validated['password']);
        $employee->save();

        return redirect()->route('nhansu.index')->with('success', 'Thêm nhân viên thành công.');
    }

    public function update(Request $request, User $user)
    {
        if (Auth::user()->role !== 'director') {
            return redirect()->route('home')
                ->with('error', 'Chỉ giám đốc được phép thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $user->id . '|regex:/^\d{10,11}$/',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'role' => 'required|in:director,manager,staff',
            'manager_id' => 'nullable|exists:users,id',
            'password' => 'nullable|string|min:8',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->manager_id = $validated['manager_id'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('nhansu.index')->with('success', 'Cập nhật nhân viên thành công.');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->role !== 'director') {
            return redirect()->route('home')
                ->with('error', 'Chỉ giám đốc được phép thực hiện thao tác này.');
        }

        $user->delete();
        return redirect()->route('nhansu.index')->with('success', 'Xóa nhân viên thành công.');
    }
}