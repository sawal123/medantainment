<?php

namespace App\Livewire;

use App\Models\Photo;
use App\Models\Alamat;
use App\Models\CategoryFilm;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Project as ModelsProject;

class Project extends Component
{
    public $setting;
    public $page;
    public $contact;
    public $categoryFilm;
    public $filmLimit = 6;
    // public $selectedCategory = 0;

    public $selectedCategory = 'all';

    public $firstCategory = '';

    public function mount($slug = null)
    {
        $this->selectedCategory = $slug ?? 'all';
        $this->setting = Setting::first();
        $this->page    = "MEDANTAINMENT - Project";
        $this->contact = Alamat::first();
        $this->firstCategory = CategoryFilm::where('slug', $slug)->first();
    }

    public function Clickfilm($slug)
    {
        $this->selectedCategory = $slug;
        $this->filmLimit = 6;
        $this->firstCategory = CategoryFilm::where('slug', $slug)->first();
        $this->dispatch('change-url', slug: $slug);
    }


    public function getFilmsProperty()
    {
        $q = ModelsProject::query();

        if ($this->selectedCategory !== 'all') {
            $q->whereHas('categoryFilm', function ($query) {
                $query->where('slug', $this->selectedCategory);
            });
        }
        // dd($q->limit($this->filmLimit)->get());

        return $q->limit($this->filmLimit)->get();
    }

    public function loadMoreFilm()
    {
        $this->filmLimit += 6;
    }

    public function render()
    {
        $this->categoryFilm = CategoryFilm::all();

        return view('livewire.project')
            ->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact
            ]);
    }
}
