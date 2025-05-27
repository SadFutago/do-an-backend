<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('roles')->group(function () {
    Route::patch('/{id}/restore', [RoleController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':roles.manage'
    ]);
    Route::delete('/{id}/force-delete', [RoleController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':roles.manage'
    ]);
    Route::post('/handle-form-actions', [RoleController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':roles.manage'
    ]);
    Route::post('/', [RoleController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':roles.manage'
    ]);
    Route::put('/{id}', [RoleController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':roles.manage'
    ]);
    Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':roles.manage'
    ]);
    Route::get('/trashed', [RoleController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':roles.view'
    ]);
    Route::get('/{id}', [RoleController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':roles.view'
    ]);
    Route::get('/', [RoleController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':roles.view'
    ]);
});