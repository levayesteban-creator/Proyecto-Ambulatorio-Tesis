<?php

namespace App\Providers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\SisDiagnosis;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Patient::observe(AuditObserver::class);
        Consultation::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
        SisDiagnosis::observe(AuditObserver::class);
    }
}
