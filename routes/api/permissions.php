<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('permissions')->group(function () {
    Route::patch('/{id}/restore', [PermissionController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':permissions.manage'
    ]);
    Route::delete('/{id}/force-delete', [PermissionController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':permissions.manage'
    ]);
    Route::post('/handle-form-actions', [PermissionController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':permissions.manage'
    ]);
    Route::post('/', [PermissionController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':permissions.manage'
    ]);
    Route::delete('/{id}', [PermissionController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':permissions.manage'
    ]);
    Route::get('/trashed', [PermissionController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':permissions.view'
    ]);
    Route::get('/{id}', [PermissionController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':permissions.view'
    ]);
    Route::get('/', [PermissionController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':permissions.view'
    ]);
});