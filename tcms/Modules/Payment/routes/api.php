<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Payment API Routes
|--------------------------------------------------------------------------
| Protected by auth:sanctum + module:payment middleware.
*/
Route::middleware(['auth:sanctum', 'module:payment'])->prefix('v1')->group(function () {
    Route::apiResource('payments', PaymentController::class)->except('create', 'edit')->names('payment');
});
