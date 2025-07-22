<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
        \App\Repositories\InvoiceRepositoryInterface::class,
        \App\Repositories\EloquentInvoiceRepository::class
        );

        $this->app->bind(
            \App\Repositories\PaymentRepositoryInterface::class,
            \App\Repositories\EloquentPaymentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
