<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| Student API Routes
|--------------------------------------------------------------------------
| Protected by auth:sanctum + module:student middleware.
*/
Route::middleware(['auth:sanctum', 'module:student'])->prefix('v1')->group(function () {
    Route::apiResource('students', StudentController::class)->except('create', 'edit')->names('student');
});
