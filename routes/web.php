<?php

use App\Http\Controllers\AutomotiveDetailingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/esteticas', [AutomotiveDetailingController::class, 'index'])->name('detailings.index');
Route::get('/esteticas/{detailing}', [AutomotiveDetailingController::class, 'show'])->name('detailings.show');
