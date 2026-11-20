<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Career;
use App\Models\Period;
use App\Models\Subject;
use App\Models\University;
use App\Policies\ActivityPolicy;
use App\Policies\CareerPolicy;
use App\Policies\PeriodPolicy;
use App\Policies\SubjectPolicy;
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
        Gate::policy(Subject::class, SubjectPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
    }
}
