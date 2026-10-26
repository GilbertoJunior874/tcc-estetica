<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DetailingController as AdminDetailingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AutomotiveDetailingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Owner\BusinessHoursController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\DetailingController;
use App\Http\Controllers\Owner\ServiceController;
use App\Http\Middleware\EnsureDetailingRegistered;
use App\Models\AutomotiveDetailing;
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
        Route::get('/estetica/cadastrar', [DetailingController::class, 'create'])->name('detailing.create');
        Route::post('/estetica', [DetailingController::class, 'store'])->name('detailing.store')->can('create', AutomotiveDetailing::class);

        Route::middleware(EnsureDetailingRegistered::class)->group(function () {
            Route::get('/estetica', [DetailingController::class, 'edit'])->name('detailing.edit');
            Route::put('/estetica', [DetailingController::class, 'update'])->name('detailing.update');
            Route::get('/horarios', [BusinessHoursController::class, 'edit'])->name('hours.edit');
            Route::put('/horarios', [BusinessHoursController::class, 'update'])->name('hours.update');

            Route::resource('servicos', ServiceController::class)
                ->except('show')
                ->parameters(['servicos' => 'service'])
                ->names('services');
            Route::patch('/servicos/{service}/status', [ServiceController::class, 'toggle'])->name('services.toggle');
        });
    });

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/esteticas', [AdminDetailingController::class, 'index'])->name('detailings.index');
        Route::get('/esteticas/{detailing}', [AdminDetailingController::class, 'show'])->name('detailings.show');
        Route::patch('/esteticas/{detailing}/aprovar', [AdminDetailingController::class, 'approve'])->name('detailings.approve');
        Route::patch('/esteticas/{detailing}/recusar', [AdminDetailingController::class, 'reject'])->name('detailings.reject');
        Route::patch('/esteticas/{detailing}/desativar', [AdminDetailingController::class, 'deactivate'])->name('detailings.deactivate');
        Route::patch('/esteticas/{detailing}/reativar', [AdminDetailingController::class, 'reactivate'])->name('detailings.reactivate');
    });
});
