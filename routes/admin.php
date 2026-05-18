<?php

use Azuriom\Plugin\Jobs\Controllers\Admin\ApplicationAdminController;
use Azuriom\Plugin\Jobs\Controllers\Admin\PositionAdminController;
use Azuriom\Plugin\Jobs\Controllers\Admin\SettingAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:jobs.manage')->group(function () {
    Route::get('/', [ApplicationAdminController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [ApplicationAdminController::class, 'show'])->name('applications.show');
    Route::patch('/applications/{application}/status', [ApplicationAdminController::class, 'updateStatus'])->name('applications.status');
    Route::delete('/applications/{application}', [ApplicationAdminController::class, 'destroy'])->name('applications.destroy');

    Route::get('/positions', [PositionAdminController::class, 'index'])->name('positions.index');
    Route::get('/positions/create', [PositionAdminController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionAdminController::class, 'store'])->name('positions.store');
    Route::get('/positions/{position}/edit', [PositionAdminController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{position}', [PositionAdminController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionAdminController::class, 'destroy'])->name('positions.destroy');
    Route::post('/positions/reorder', [PositionAdminController::class, 'reorder'])->name('positions.reorder');

    Route::get('/settings', [SettingAdminController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingAdminController::class, 'update'])->name('settings.update');
});
