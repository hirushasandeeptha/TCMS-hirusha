<?php

use Illuminate\Support\Facades\Route;
use Modules\ClassSchedule\Http\Controllers\ClassScheduleController;

/*
|--------------------------------------------------------------------------
| ClassSchedule Module Web Routes (Inertia)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'module:classschedule'])->prefix('class-schedules')->name('class-schedules.')->group(function () {
    Route::get('/', [ClassScheduleController::class, 'index'])->name('index');
    Route::post('/', [ClassScheduleController::class, 'store'])->name('store');
    Route::get('/create', fn () => \Inertia\Inertia::render('ClassSchedules/Create'))->name('create');
    Route::get('/{classSchedule}', [ClassScheduleController::class, 'show'])->name('show');
    Route::get('/{classSchedule}/edit', [ClassScheduleController::class, 'edit'])->name('edit');
    Route::put('/{classSchedule}', [ClassScheduleController::class, 'update'])->name('update');
    Route::delete('/{classSchedule}', [ClassScheduleController::class, 'destroy'])->name('destroy');
});
