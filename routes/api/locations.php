<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('locations')->group(function () {
    Route::patch('/{id}/restore', [LocationController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':locations.manage'
    ]);
    Route::delete('/{id}/force-delete', [LocationController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':locations.manage'
    ]);
    Route::post('/handle-form-actions', [LocationController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':locations.manage'
    ]);
    Route::post('/', [LocationController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':locations.manage'
    ]);
    Route::put('/{id}', [LocationController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':locations.manage'
    ]);
    Route::delete('/{id}', [LocationController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':locations.manage'
    ]);
    Route::get('/trashed', [LocationController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':locations.view'
    ]);
    Route::get('/{id}', [LocationController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':locations.view'
    ]);
    Route::get('/', [LocationController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':locations.view'
    ]);
});