<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaDashboardController;

Route::middleware(['auth', 'verified'])->prefix('dashboard/media')->name('media.')->group(function () {
    Route::get('/', [MediaDashboardController::class, 'index'])->name('index');
    Route::post('/upload', [MediaDashboardController::class, 'upload'])->name('upload');
    Route::delete('/{id}', [MediaDashboardController::class, 'destroy'])->name('destroy');
});
