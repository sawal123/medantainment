<?php

namespace App\Providers;

use App\Models\AboutUs;
use App\Models\Alamat;
use App\Models\Blog;
use App\Models\Candidate;
use App\Models\Carrer;
use App\Models\Category;
use App\Models\CategoryFilm;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Hero;
use App\Models\Internship;
use App\Models\Landing;
use App\Models\Photo;
use App\Models\PhotoLanding;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Slide;
use App\Models\Sosmed;
use App\Models\Tag;
use App\Models\Team;
use App\Models\Testimoni;
use App\Models\User;
use App\Policies\AdminOnlyPolicy;
use App\Policies\BlogPolicy;
use App\Policies\CandidatePolicy;
use App\Policies\InternshipPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Pemetaan model ke policy aplikasi.
     * Semua resource non-blog didaftarkan khusus admin-only.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AboutUs::class => AdminOnlyPolicy::class,
        Alamat::class => AdminOnlyPolicy::class,
        Blog::class => BlogPolicy::class,
        Candidate::class => CandidatePolicy::class,
        Carrer::class => AdminOnlyPolicy::class,
        Category::class => AdminOnlyPolicy::class,
        CategoryFilm::class => AdminOnlyPolicy::class,
        Client::class => AdminOnlyPolicy::class,
        Comment::class => AdminOnlyPolicy::class,
        Hero::class => AdminOnlyPolicy::class,
        Internship::class => InternshipPolicy::class,
        Landing::class => AdminOnlyPolicy::class,
        Photo::class => AdminOnlyPolicy::class,
        PhotoLanding::class => AdminOnlyPolicy::class,
        Project::class => AdminOnlyPolicy::class,
        Setting::class => AdminOnlyPolicy::class,
        Slide::class => AdminOnlyPolicy::class,
        Sosmed::class => AdminOnlyPolicy::class,
        Tag::class => AdminOnlyPolicy::class,
        Team::class => AdminOnlyPolicy::class,
        Testimoni::class => AdminOnlyPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
