<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notification;
use App\Models\Task;

Route::middleware('auth:sanctum')->get('/notifications', function (Request $request) {
    return response()->json([
        'notifications' => Notification::latest()->take(5)->get(),
        'tasks' => Task::latest()->get()
    ]);
});
?>