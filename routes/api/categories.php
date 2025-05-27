<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('categories')->group(function () {
    Route::get('/by-slug/{slug}', [CategoryController::class, 'getBySlug']);
    Route::patch('/{id}/restore', [CategoryController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':categories.manage'
    ]);
    Route::delete('/{id}/force-delete', [CategoryController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':categories.manage'
    ]);
    Route::post('/handle-form-actions', [CategoryController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':categories.manage'
    ]);
    Route::post('/', [CategoryController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':categories.manage'
    ]);
    Route::put('/{id}', [CategoryController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':categories.manage'
    ]);
    Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':categories.manage'
    ]);
    Route::get('/trashed', [CategoryController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':categories.view'
    ]);
    Route::get('/{id}', [CategoryController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':categories.view'
    ]);
    Route::get('/', [CategoryController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':categories.view'
    ]);
});