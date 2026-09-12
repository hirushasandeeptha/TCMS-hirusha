<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Notification Module Web Routes (Inertia)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'module:notification'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/', [NotificationController::class, 'store'])->name('store');
    Route::get('/create', fn () => \Inertia\Inertia::render('Notifications/Create'))->name('create');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
    Route::put('/{notification}', [NotificationController::class, 'update'])->name('update');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});
