<?php

use App\Http\Controllers\PrivateFileController;
use App\Livewire\AboutUs;
use App\Livewire\Blog;
use App\Livewire\BlogDetail;
use App\Livewire\Carrer;
use App\Livewire\CarrerDetail;
use App\Livewire\CarrerForm;
use App\Livewire\Contact;
use App\Livewire\Gallery;
use App\Livewire\Home;
use App\Livewire\Project;
use App\Livewire\Team;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Publik
|--------------------------------------------------------------------------
*/

Route::get('/', Home::class)->name('index');
Route::get('/project', Project::class);
Route::get('/project/{slug}', Project::class);
Route::get('/team', Team::class);
Route::get('/gallery', Gallery::class);
Route::get('/blog/{search?}', Blog::class);
Route::get('/blog/detail/{slug}', BlogDetail::class);
Route::get('/career', Carrer::class);
Route::get('/career/detail/{slug}', CarrerDetail::class);
Route::get('/career/form/{slug}', CarrerForm::class);
Route::get('/contact', Contact::class);
Route::get('/about-us', AboutUs::class);

/*
|--------------------------------------------------------------------------
| Private File Downloads (terproteksi — hanya admin)
|--------------------------------------------------------------------------
|
| Endpoint ini memerlukan autentikasi. Admin-check dilakukan di controller.
| Path file diambil dari database (bukan dari URL) untuk mencegah traversal.
|
*/
Route::middleware('auth')->prefix('secure')->name('secure.')->group(function () {
    Route::get(
        '/candidate/{candidate}/download/{field}',
        [PrivateFileController::class, 'downloadCandidate']
    )->name('candidate.download');

    Route::get(
        '/internship/{internship}/download/{field}',
        [PrivateFileController::class, 'downloadInternship']
    )->name('internship.download');
});
