<?php

namespace App\Providers;

use App\Contracts\AccountingService;
use App\Services\QuickBooksService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AccountingService::class, function(){
            return new QuickBooksService(
                config('quickbooks.key'),
                config('quickbooks.secret'),
                config('quickbooks.redirect'),
                config('quickbooks.environment'),
                config('quickbooks.base_url'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
