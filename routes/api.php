<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::apiResource('media', MediaController::class)->names('media');
});
