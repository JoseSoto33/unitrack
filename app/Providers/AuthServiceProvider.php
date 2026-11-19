<?php

namespace App\Providers;

use App\Models\Career;
use App\Models\Period;
use App\Models\University;
use App\Policies\CareerPolicy;
use App\Policies\PeriodPolicy;
use App\Policies\UniversityPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(University::class, UniversityPolicy::class);
        Gate::policy(Career::class, CareerPolicy::class);
        Gate::policy(Period::class, PeriodPolicy::class);
    }
}
