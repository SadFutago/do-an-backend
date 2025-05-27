<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('units')->group(function () {
    Route::patch('/{id}/restore', [UnitController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':units.manage'
    ]);
    Route::delete('/{id}/force-delete', [UnitController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':units.manage'
    ]);
    Route::post('/handle-form-actions', [UnitController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':units.manage'
    ]);
    Route::post('/', [UnitController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':units.manage'
    ]);
    Route::put('/{id}', [UnitController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':units.manage'
    ]);
    Route::delete('/{id}', [UnitController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':units.manage'
    ]);
    Route::get('/trashed', [UnitController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':units.view'
    ]);
    Route::get('/{id}', [UnitController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':units.view'
    ]);
    Route::get('/', [UnitController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':units.view'
    ]);
});