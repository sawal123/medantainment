<?php

namespace App\Livewire;

use App\Models\Photo;
use App\Models\Project as ModelsProject;
use App\Models\Setting;
use Livewire\Component;

class Project extends Component
{
    public $setting;
    public $page;
    public $film;
    public $image;

    public $showFilm = true;
    public $showImage = false;

    public $filmLimit = 6;
    public $imageLimit = 6;

    public function Clickfilm()
    {
        $this->showFilm = true;
        $this->showImage = false;
    }
    public function Clickphoto()
    {
        $this->showFilm = false;
        $this->showImage = true;
    }


    public function loadData()
    {
        $this->film = ModelsProject::take($this->filmLimit)->get();
        $this->image = Photo::take($this->imageLimit)->get();
    }

    public function loadMoreFilm()
    {
        $this->filmLimit += 6;
        $this->loadData();
    }

    public function loadMoreImage()
    {
        $this->imageLimit += 6;
        $this->loadData();
    }

    public function mount()
    {
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Project";


        $this->loadData();

        // $this->film = ModelsProject::all();
        // $this->image = Photo::all();
    }
    public function render()
    {
        $cekFilm = Project::all();
        $cekPhoto = Photo::all();
        return view('livewire.project', ['cekFilm'=>$cekFilm, 'cekPhoto'=>$cekPhoto])->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting
        ]);;
    }
}
