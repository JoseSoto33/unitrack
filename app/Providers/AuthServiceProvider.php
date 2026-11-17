<?php

namespace App\Providers;

use App\Models\University;
use App\Policies\UniversityPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(University::class, UniversityPolicy::class);
    }
}
