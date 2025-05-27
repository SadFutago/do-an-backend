<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('inventories')->group(function () {
    Route::patch('/{id}/restore', [InventoryController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':inventories.manage'
    ]);
    Route::delete('/{id}/force-delete', [InventoryController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':inventories.manage'
    ]);
    Route::post('/handle-form-actions', [InventoryController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':inventories.manage'
    ]);
    Route::post('/', [InventoryController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':inventories.manage'
    ]);
    Route::put('/{id}', [InventoryController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':inventories.manage'
    ]);
    Route::delete('/{id}', [InventoryController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':inventories.manage'
    ]);
    Route::get('/trashed', [InventoryController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':inventories.view'
    ]);
    Route::get('/{id}', [InventoryController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':inventories.view'
    ]);
    Route::get('/', [InventoryController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':inventories.view'
    ]);
});