<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Notification API Routes
|--------------------------------------------------------------------------
| Protected by auth:sanctum + module:notification middleware.
*/
Route::middleware(['auth:sanctum', 'module:notification'])->prefix('v1')->group(function () {
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notification.mark-read');
    Route::apiResource('notifications', NotificationController::class)
        ->except('edit')
        ->names('notification');
});
