<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Services\SaleService::class,
            function ($app) {
                return new \App\Services\SaleService(
                    $app->make(\App\Services\AccountingService::class),
                    $app->make(\App\Services\InventoryService::class)
                );
            }
        );

        $this->app->bind(
            \App\Services\AI\SmartERP::class,
            function ($app) {
                return new \App\Services\AI\SmartERP(
                    $app->make(\App\Services\InventoryService::class)
                );
            }
        );
    }

    public function boot(): void
    {
        //
    }
}
