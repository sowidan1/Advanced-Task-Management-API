<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('tasks')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    Route::get('search', [TaskController::class, 'search']);
    Route::patch('{task}/status', [TaskController::class, 'updateStatus']);
    Route::post('/', [TaskController::class, 'store']);
    Route::apiResource('/', TaskController::class)->except(['store']);
});
