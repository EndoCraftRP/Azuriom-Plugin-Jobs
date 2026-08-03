<?php

use Azuriom\Plugin\Jobs\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ApplicationController::class, 'index'])->name('index');
Route::get('/{position:slug}', [ApplicationController::class, 'show'])->name('show');
Route::post('/{position:slug}', [ApplicationController::class, 'store'])->middleware('auth')->name('store');
Route::get('/my-application/{application}', [ApplicationController::class, 'status'])->middleware('auth')->name('status');
Route::delete('/my-application/{application}', [ApplicationController::class, 'cancel'])->middleware('auth')->name('cancel');

Route::get('/applications/{application}/attachments/{filename}', [ApplicationController::class, 'downloadAttachment'])
    ->middleware('auth')
    ->name('attachments.download');

Route::delete('/applications/{application}/fields/{fieldId}/attachments/{index}', [ApplicationController::class, 'deleteAttachment'])
    ->middleware('auth')
    ->name('attachments.delete');
