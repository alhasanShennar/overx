<?php

namespace App\Providers;

use App\Models\CashoutDetail;
use App\Models\EarningPeriod;
use App\Policies\CashoutDetailPolicy;
use App\Policies\EarningPeriodPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        EarningPeriod::class => EarningPeriodPolicy::class,
        CashoutDetail::class => CashoutDetailPolicy::class,
    ];

    public function boot(): void
    {
        // Admin gate: super_admin or admin role model
        Gate::define('admin-access', function ($user) {
            return $user->isAdmin();
        });
    }
}
