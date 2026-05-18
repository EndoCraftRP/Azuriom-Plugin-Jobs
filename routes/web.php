<?php

use Azuriom\Plugin\Jobs\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ApplicationController::class, 'index'])->name('index');
Route::get('/{position:slug}', [ApplicationController::class, 'show'])->name('show');
Route::post('/{position:slug}', [ApplicationController::class, 'store'])->middleware('auth')->name('store');
Route::get('/my-application/{application}', [ApplicationController::class, 'status'])->middleware('auth')->name('status');
Route::delete('/my-application/{application}', [ApplicationController::class, 'cancel'])->middleware('auth')->name('cancel');
