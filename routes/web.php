<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Dashboard (with live stats + module list)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Module Subscribe / Unsubscribe (Inertia + JSON)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('modules')->name('modules.')->group(function () {
    Route::post('/subscribe/{module:slug}', [ModuleController::class, 'subscribe'])->name('subscribe');
    Route::delete('/unsubscribe/{module:slug}', [ModuleController::class, 'unsubscribe'])->name('unsubscribe');
});

/*
|--------------------------------------------------------------------------
| SaaS Marketplace
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Marketplace/Index');
    })->name('index');

    Route::get('/data', [ModuleController::class, 'index'])->name('data');
});

/*
|--------------------------------------------------------------------------
| Profile (Breeze default)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
