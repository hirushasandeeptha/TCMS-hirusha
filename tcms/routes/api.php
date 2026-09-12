<?php

use App\Http\Controllers\ModuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Main app API routes. Module API routes are loaded separately by nwidart.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Marketplace API (JSON endpoints for Vue frontend)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('marketplace')->name('api.marketplace.')->group(function () {
    Route::get('/', [ModuleController::class, 'index'])->name('index');
    Route::post('/subscribe/{module:slug}', [ModuleController::class, 'subscribe'])->name('subscribe');
    Route::delete('/unsubscribe/{module:slug}', [ModuleController::class, 'unsubscribe'])->name('unsubscribe');
});

/*
|--------------------------------------------------------------------------
| Students API (loaded by nwidart module, but also registered here for reference)
|--------------------------------------------------------------------------
| Student module API routes are at api/v1/students via nwidart StudentServiceProvider.
| They use auth:sanctum middleware and the Tenantable scope.
*/
