<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('users')->group(function () {
    Route::middleware([AuthenticateWithJWT::class])->group(function () {
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::patch('/password/reset', [UserController::class, 'resetPassword']);
    });
    Route::patch('/{id}/restore', [UserController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':users.manage'
    ]);
    Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':users.manage'
    ]);
    Route::post('/handle-form-actions', [UserController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':users.manage'
    ]);
    Route::post('/', [UserController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':users.manage'
    ]);
    Route::put('/{id}', [UserController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':users.manage'
    ]);
    Route::delete('/{id}', [UserController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':users.manage'
    ]);
    Route::get('/trashed', [UserController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':users.view'
    ]);
    Route::get('/{id}', [UserController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':users.view'
    ]);
    Route::get('/', [UserController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':users.view'
    ]);
});

