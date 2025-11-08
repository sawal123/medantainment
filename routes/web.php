<?php

use App\Livewire\AboutUs;
use App\Livewire\Blog;
use App\Livewire\BlogDetail;
use App\Livewire\Carrer;
use App\Livewire\CarrerDetail;
use App\Livewire\CarrerForm;
use App\Livewire\Contact;
use App\Livewire\Gallery;
use App\Livewire\Home;
use App\Livewire\Index;
use App\Livewire\Project;
use App\Livewire\Team;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', Home::class)->name('index');
Route::get('/project', Project::class);
Route::get('/project/{slug}', \App\Livewire\Project::class);
Route::get('/team', Team::class);
Route::get('/gallery', Gallery::class);
Route::get('/blog/{search?}', Blog::class);
Route::get('/blog/detail/{slug}', BlogDetail::class);
Route::get('/carrer', Carrer::class);
Route::get('/carrer/detail/{slug}', CarrerDetail::class);
Route::get('/carrer/form/{slug}', CarrerForm::class);
Route::get('/contact', Contact::class);
Route::get('/about-us', AboutUs::class);
// Route::get('/', Index::class)->name('index');
