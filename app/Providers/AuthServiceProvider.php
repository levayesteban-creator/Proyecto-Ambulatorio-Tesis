<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Patient;
use App\Models\Consultation;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Policies\PatientPolicy;
use App\Policies\ConsultationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Patient::class => PatientPolicy::class,
        Consultation::class => ConsultationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-audit-logs', function (User $user) {
            return in_array($user->role_id, [Role::ADMIN, Role::COORDINATOR]);
        });
    }
}

