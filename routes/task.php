<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    Route::get('tasks/search', [TaskController::class, 'search']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::post('tasks', [TaskController::class, 'store']);

    Route::apiResource('tasks', TaskController::class)->except(['store']);
});
