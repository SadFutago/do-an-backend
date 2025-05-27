<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AddressService;

class LocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AddressService::class, function ($app) {
            return new AddressService();
        });
    }

    public function boot(): void
    {
        //
    }
}