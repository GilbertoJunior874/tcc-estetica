<?php

use App\Http\Controllers\AutomotiveDetailingController;
use Illuminate\Support\Facades\Route;

Route::get('/esteticas', [AutomotiveDetailingController::class, 'index'])->name('detailings.index');
