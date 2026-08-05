<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\CategoryFilm;
use App\Models\Project as ModelsProject;
use App\Models\ProjectSeries;
use App\Models\Setting;
use Livewire\Component;

class Project extends Component
{
    public $setting;

    public $page;

    public $contact;

    public $categoryFilm;

    public $selectedSeries;

    public $filmLimit = 8;
    // public $selectedCategory = 0;

    public $selectedCategory = 'all';

    public $firstCategory = '';

    public function mount($slug = null)
    {
        $this->setting = Setting::first();
        $this->page = 'MEDANTAINMENT - Project';
        $this->contact = Alamat::first();

        if ($slug) {
            $series = ProjectSeries::where('slug', $slug)->first();
            if ($series) {
                $this->selectedSeries = $series;
                $this->selectedCategory = 'all';
                $this->firstCategory = $series;
                return;
            }

            $category = CategoryFilm::where('slug', $slug)->first();
            $this->selectedCategory = $category ? $category->slug : 'all';
            $this->firstCategory = $category;
            return;
        }

        $this->selectedCategory = 'all';
        $this->firstCategory = '';
    }

    public function Clickfilm($slug)
    {
        $this->selectedSeries = null;
        $this->selectedCategory = $slug;
        $this->filmLimit = 8;
        $this->firstCategory = CategoryFilm::where('slug', $slug)->first();
        $this->dispatch('change-url', slug: $slug);
    }

    public function getFilmsProperty()
    {
        if ($this->selectedSeries) {
            return $this->selectedSeries->episodes()->orderBy('urutan', 'asc')->limit($this->filmLimit)->get();
        }

        $q = ModelsProject::query();

        if ($this->selectedCategory !== 'all') {
            $q->whereHas('categoryFilm', function ($query) {
                $query->where('slug', $this->selectedCategory);
            });
        }

        return $q->orderBy('urutan', 'asc')->limit($this->filmLimit)->get();
    }

    public function getTotalFilmsProperty()
    {
        if ($this->selectedSeries) {
            return $this->selectedSeries->episodes()->count();
        }

        $q = ModelsProject::query();

        if ($this->selectedCategory !== 'all') {
            $q->whereHas('categoryFilm', function ($query) {
                $query->where('slug', $this->selectedCategory);
            });
        }

        return $q->count();
    }

    public function loadMoreFilm()
    {
        $this->filmLimit = min($this->filmLimit + 6, $this->totalFilms);
    }

    public function render()
    {
        $this->categoryFilm = CategoryFilm::orderBy('urutan', 'asc')->get();

        $films = $this->films;
        $totalFilms = $this->totalFilms;

        return view('livewire.project', compact('films', 'totalFilms'))
            ->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact,
            ]);
    }
}
