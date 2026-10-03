<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AutomotiveDetailingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Owner\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/esteticas', [AutomotiveDetailingController::class, 'index'])->name('detailings.index');
Route::get('/esteticas/{detailing}', [AutomotiveDetailingController::class, 'show'])->name('detailings.show');

Route::middleware('guest')->group(function () {
    Route::get('/cadastro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/entrar', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/entrar', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/sair', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('painel')->name('owner.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
    });
});
