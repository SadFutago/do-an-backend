<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SortMiddleware;
use App\Http\Controllers\SupplierController;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('suppliers')->group(function () {
    Route::patch('/{id}/restore', [SupplierController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':suppliers.manage'
    ]);
    Route::delete('/{id}/force-delete', [SupplierController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':suppliers.manage'
    ]);
    Route::post('/handle-form-actions', [SupplierController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':suppliers.manage'
    ]);
    Route::post('/', [SupplierController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':suppliers.manage'
    ]);
    Route::put('/{id}', [SupplierController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':suppliers.manage'
    ]);
    Route::delete('/{id}', [SupplierController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':suppliers.manage'
    ]);
    Route::get('/trashed', [SupplierController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':suppliers.view'
    ]);
    Route::get('/{id}', [SupplierController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':suppliers.view'
    ]);
    Route::get('/', [SupplierController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':suppliers.view'
    ]);
});