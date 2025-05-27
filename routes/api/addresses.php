<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;

Route::prefix('addresses')->group(function () {
    Route::get('/provinces', [AddressController::class, 'provinces']);
    Route::get('/provinces/{id}/districts', [AddressController::class, 'provinceWithDistricts']);
    Route::get('/districts/{id}/wards', [AddressController::class, 'districtWithWards']);
});