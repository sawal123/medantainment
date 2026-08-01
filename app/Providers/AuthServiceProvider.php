<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\Candidate;
use App\Models\Internship;
use App\Models\User;
use App\Policies\BlogPolicy;
use App\Policies\CandidatePolicy;
use App\Policies\InternshipPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     * Didaftarkan secara eksplisit agar tidak bergantung pada auto-discovery.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Blog::class      => BlogPolicy::class,
        Candidate::class => CandidatePolicy::class,
        Internship::class => InternshipPolicy::class,
        User::class      => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
