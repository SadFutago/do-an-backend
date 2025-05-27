<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('products')->group(function () {
    Route::get('/by-slug/{slug}', [ProductController::class, 'getBySlug']);
    Route::get('/by-category/{slug?}', [ProductController::class, 'getByCategorySlug'])->middleware(SortMiddleware::class);
    // admin routes
    Route::patch('/{id}/restore', [ProductController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':products.manage'
    ]);
    Route::delete('/{id}/force-delete', [ProductController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':products.manage'
    ]);
    Route::post('/handle-form-actions', [ProductController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':products.manage'
    ]);
    Route::post('/', [ProductController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':products.manage'
    ]);
    Route::put('/{id}', [ProductController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':products.manage'
    ]);
    Route::get('/trashed', [ProductController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':products.view'
    ]);
    Route::get('/{id}', [ProductController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':products.view'
    ]);
    Route::get('/', [ProductController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':products.view'
    ]);
});