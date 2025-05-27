<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;
use App\Http\Middleware\Authorization;

Route::prefix('articles')->group(function () {
    Route::get('/by-slug/{slug}', [ArticleController::class, 'getBySlug']);
    Route::get('/by-category/{categorySlug}', [ArticleController::class, 'getByCategory']);
    Route::get('/published', [ArticleController::class, 'getPublished'])->middleware(SortMiddleware::class);
    Route::patch('/{id}/approve', [ArticleController::class, 'approve'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.approve'
    ]);
    Route::patch('/{id}/restore', [ArticleController::class, 'restore'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.manage'
    ]);
    Route::delete('/{id}/force-delete', [ArticleController::class, 'forceDelete'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.manage'
    ]);
    Route::post('/handle-form-actions', [ArticleController::class, 'handleFormActions'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.manage'
    ]);
    Route::post('/', [ArticleController::class, 'store'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.manage'
    ]);
    Route::put('/{id}', [ArticleController::class, 'update'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.manage'
    ]);
    Route::delete('/{id}', [ArticleController::class, 'destroy'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.manage'
    ]);
    Route::get('/trashed', [ArticleController::class, 'trashed'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':articles.view'
    ]);
    Route::get('/{id}', [ArticleController::class, 'show'])->middleware([
        AuthenticateWithJWT::class, Authorization::class . ':articles.view'
    ]);
    Route::get('/', [ArticleController::class, 'index'])->middleware([
        SortMiddleware::class, AuthenticateWithJWT::class, Authorization::class . ':articles.view'
    ]);
});