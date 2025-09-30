<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NhansuController;

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

    // Tasks CRUD với view calendar
    Route::get('/tasks/calendar', [TaskController::class, 'index'])->name('tasks.calendar');
    Route::resource('tasks', TaskController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');
    Route::get('/reports/export', [ReportController::class, 'exportPDF'])->name('reports.export');

    //nhân sự
    Route::get('/nhansu', [NhansuController::class, 'index'])->name('nhansu.index');
Route::post('/nhansu', [NhansuController::class, 'store'])->name('nhansu.store');
Route::put('/nhansu/{user}', [NhansuController::class, 'update'])->name('nhansu.update');
Route::delete('/nhansu/{user}', [NhansuController::class, 'destroy'])->name('nhansu.destroy');
});