<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Blog;
use App\Models\CategoryFilm;
use App\Models\client;
use App\Models\Hero;
use App\Models\Landing;
use App\Models\Photo;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Slide;
use App\Models\Sosmed;
use App\Models\Team;
use App\Models\Testimoni;
use Livewire\Component;

error_reporting(0);
class Home extends Component
{
    public $testimoni;
    public $project;
    public $landing;
    public $blog;
    public $client;
    public $setting;
    public $page;
    public $contact;
    public $sosmed;
    public $team;
    public $slide;
    public $hero;
    public $photo;
    public $categoryFilm;

    public $showAll = false;



    public function loadClients()
    {
        $this->client = $this->showAll
            ? Client::orderBy('urutan')->where('status', '1')->get()
            : Client::orderBy('urutan')->where('status', '1')->limit(6)->get();
    }


    public function tes()
    {
        $this->showAll = true;
        $this->loadClients();
    }
    public function mount()
    {
        error_reporting(0);
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT";
        $this->team = Team::all();
        $this->hero = Hero::pluck('title', 'hero_type');
        // $this->client = client::all();
        $this->loadClients();
        $this->blog = Blog::all();
        $this->testimoni = Testimoni::all();
        $this->project = Project::all();
        $this->contact = Alamat::first();
        $this->sosmed = Sosmed::all();
        $this->landing = Landing::pluck('value')->toArray();
        $this->slide =  Slide::all();
        $this->categoryFilm =  CategoryFilm::orderBy('urutan')->get();
        $this->photo = Photo::limit(6)->get();
    }
    public function render()
    {

        // $hero = Hero::pluck('title', 'hero_type');
        // dd($hero['hero2']);
        return view('livewire.home')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact' => $this->contact,
            'heros' => $this->hero
        ]);
    }
}
