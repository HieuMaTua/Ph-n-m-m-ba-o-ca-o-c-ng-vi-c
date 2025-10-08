<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NhansuController;
use App\Http\Controllers\TaskFileController;
use App\Http\Controllers\TaskRequestController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Các route cần đăng nhập
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // Báo cáo công việc
    Route::get('/tasks/baocaocv', [TaskFileController::class, 'index'])->name('tasks.report');
    Route::post('/tasks/baocaocv', [TaskFileController::class, 'store'])->name('tasks.report.store');
    Route::delete('/tasks/baocaocv/{id}', [TaskFileController::class, 'destroy'])->name('tasks.report.destroy');
    Route::get('/tasks/{taskId}/comments', [TaskFileController::class, 'comments'])->name('tasks.comments');

    // Tasks CRUD với view calendar
    Route::get('/tasks/calendar', [TaskController::class, 'index'])->name('tasks.calendar');
    Route::resource('tasks', TaskController::class);
    Route::delete('/tasks/files/{file}', [TaskController::class, 'destroyFile'])->name('tasks.destroyFile'); // Thêm route này

    Route::resource('task_requests', TaskRequestController::class)->only(['index', 'store']);
    Route::post('task_requests/{id}/approve', [TaskRequestController::class, 'approve'])->name('task_requests.approve');
    Route::post('task_requests/{id}/reject', [TaskRequestController::class, 'reject'])->name('task_requests.reject');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');
    Route::get('/reports/export', [ReportController::class, 'exportPDF'])->name('reports.export');

    // Nhân sự
    Route::get('/nhansu', [NhansuController::class, 'index'])->name('nhansu.index');
    Route::post('/nhansu', [NhansuController::class, 'store'])->name('nhansu.store');
    Route::put('/nhansu/{user}', [NhansuController::class, 'update'])->name('nhansu.update');
    Route::delete('/nhansu/{user}', [NhansuController::class, 'destroy'])->name('nhansu.destroy');

    // API routes cho dashboard
    Route::get('/api/tasks', [DashboardController::class, 'apiStats'])->name('api.tasks.stats');
    Route::post('/api/tasks/{task}/dismiss-reminder', [DashboardController::class, 'dismissReminder'])->name('api.tasks.dismiss-reminder');
});