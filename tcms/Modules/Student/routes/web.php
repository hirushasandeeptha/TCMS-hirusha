<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| Student Module Web Routes (Inertia)
|--------------------------------------------------------------------------
| These are loaded by nwidart's RouteServiceProvider.
| The 'module:student' middleware checks subscription access.
*/
Route::middleware(['auth', 'module:student'])->prefix('students')->name('students.')->group(function () {
    Route::get('/', [StudentController::class, 'index'])->name('index');
    Route::post('/', [StudentController::class, 'store'])->name('store');
    Route::get('/create', fn () => \Inertia\Inertia::render('Students/Create'))->name('create');
    Route::get('/{student}', [StudentController::class, 'show'])->name('show');
    Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
    Route::put('/{student}', [StudentController::class, 'update'])->name('update');
    Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
});
