<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Booking;
use App\Policies\BookingPolicy;
use App\Contracts\PaymentGateway;
use App\Services\Payments\SimulatedPaymentGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, SimulatedPaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
