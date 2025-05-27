<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoucherController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;

Route::prefix('vouchers')->group(function () {
    Route::patch('/{id}/approve', [VoucherController::class, 'approve'])->middleware(AuthenticateWithJWT::class);
    Route::get('/check/{couponCode}', [VoucherController::class, 'checkCoupon'])->middleware(AuthenticateWithJWT::class);
    Route::get('/trashed', [VoucherController::class, 'trashed'])->middleware(SortMiddleware::class);
    Route::patch('/{id}/restore', [VoucherController::class, 'restore']);
    Route::delete('/{id}/force-delete', [VoucherController::class, 'forceDelete']);
    Route::post('/handle-form-actions', [VoucherController::class, 'handleFormActions']);
    Route::post('/', [VoucherController::class, 'store']);
    Route::put('/{id}', [VoucherController::class, 'update']);
    Route::delete('/{id}', [VoucherController::class, 'destroy']);
    Route::get('/{id}', [VoucherController::class, 'show']);
    Route::get('/', [VoucherController::class, 'index'])->middleware(SortMiddleware::class);
});