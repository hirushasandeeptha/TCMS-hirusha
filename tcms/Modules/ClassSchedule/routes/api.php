<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassSchedule\Http\Controllers\ClassScheduleController;

/*
|--------------------------------------------------------------------------
| ClassSchedule API Routes
|--------------------------------------------------------------------------
| Protected by auth:sanctum + module:classschedule middleware.
*/
Route::middleware(['auth:sanctum', 'module:classschedule'])->prefix('v1')->group(function () {
    Route::apiResource('class-schedules', ClassScheduleController::class)
        ->except('create', 'edit')
        ->names('class-schedule');
});
