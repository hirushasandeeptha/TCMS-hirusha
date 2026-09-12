<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Attendance API Routes
|--------------------------------------------------------------------------
| Protected by auth:sanctum + module:attendance middleware.
*/
Route::middleware(['auth:sanctum', 'module:attendance'])->prefix('v1')->group(function () {
    Route::post('attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
    Route::apiResource('attendances', AttendanceController::class)->except('create', 'edit')->names('attendance');
});
