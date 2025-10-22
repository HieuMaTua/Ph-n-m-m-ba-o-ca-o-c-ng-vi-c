<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Models\SettingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function __construct()
    {
        // Chỉ giám đốc được truy cập vào các route roles và system
        $this->middleware('director')->only(['updateRoles', 'updateSystem']);
    }

    public function index()
    {
        $employees = User::with('manager')->paginate(10);
        $managers = User::whereIn('role', ['director', 'manager'])->get();
        $settings = Cache::remember('settings', now()->addHours(1), fn() => Setting::pluck('value', 'key')->toArray());
        return view('settings.index', compact('employees', 'managers', 'settings'));
    }

    public function updatePersonal(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'phone' => 'required|string|regex:/^\d{10,11}$/|unique:users,phone,' . Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email ?: null;
        $user->phone = $request->phone;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thông tin cá nhân thành công']);
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:director,manager,staff',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $user->role = $request->role;
        $user->manager_id = $request->manager_id ?: null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật vai trò thành công']);
    }

    public function updateSystem(Request $request)
    {
        $request->validate([
            'max_file_size' => 'required|integer|min:1|max:100',
            'max_tasks_per_user' => 'required|integer|min:1|max:1000',
        ]);

        $oldFileSize = Setting::where('key', 'max_file_size')->value('value');
        $oldTasks = Setting::where('key', 'max_tasks_per_user')->value('value');

        Setting::updateOrCreate(['key' => 'max_file_size'], ['value' => $request->max_file_size]);
        Setting::updateOrCreate(['key' => 'max_tasks_per_user'], ['value' => $request->max_tasks_per_user]);

        // Lưu lịch sử thay đổi
        if ($oldFileSize != $request->max_file_size) {
            SettingLog::create([
                'user_id' => Auth::id(),
                'key' => 'max_file_size',
                'old_value' => $oldFileSize,
                'new_value' => $request->max_file_size,
            ]);
        }
        if ($oldTasks != $request->max_tasks_per_user) {
            SettingLog::create([
                'user_id' => Auth::id(),
                'key' => 'max_tasks_per_user',
                'old_value' => $oldTasks,
                'new_value' => $request->max_tasks_per_user,
            ]);
        }

        Cache::forget('settings'); // Xóa cache khi cập nhật
        return response()->json(['success' => true, 'message' => 'Cập nhật cấu hình hệ thống thành công']);
    }
}