<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notification;
use App\Models\Task;

Route::post('/update-fcm-token', [UserController::class, 'updateFcmToken']);

Route::middleware('auth:sanctum')->get('/notifications', function (Request $request) {
    return response()->json([
        'notifications' => Notification::latest()->take(5)->get(),
        'tasks' => Task::latest()->get()
    ]);
});
?>