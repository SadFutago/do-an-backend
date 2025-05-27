<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PromotionController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('promotions')->group(function () {
    Route::patch('/{id}/approve', [PromotionController::class, 'approve'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.approve'
    ]);
    Route::patch('/{id}/restore', [PromotionController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.manage'
    ]);
    Route::delete('/{id}/force-delete', [PromotionController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.manage'
    ]);
    Route::post('/handle-form-actions', [PromotionController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.manage'
    ]);
    Route::post('/', [PromotionController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.manage'
    ]);
    Route::put('/{id}', [PromotionController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.manage'
    ]);
    Route::delete('/{id}', [PromotionController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.manage'
    ]);
    Route::get('/trashed', [PromotionController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':promotions.view'
    ]);
    Route::get('/{id}', [PromotionController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':promotions.view'
    ]);
    Route::get('/', [PromotionController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':promotions.view'
    ]);
});