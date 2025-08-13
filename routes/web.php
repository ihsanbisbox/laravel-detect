<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnimalDetectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [AnimalDetectionController::class, 'index'])->name('detection.index');
Route::post('/detect', [AnimalDetectionController::class, 'detect'])->name('detection.detect');
Route::get('/history', [AnimalDetectionController::class, 'history'])->name('detection.history');

// Optional: API routes jika diperlukan
Route::prefix('api')->group(function () {
    Route::post('/detect', [AnimalDetectionController::class, 'detectApi'])->name('api.detection.detect');
    Route::get('/flask-health', [AnimalDetectionController::class, 'checkFlaskHealth'])->name('api.flask.health');
});