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

    public function getSeriesListProperty()
    {
        $query = ProjectSeries::query()
            ->where('is_active', true)
            ->withCount('episodes')
            ->orderBy('urutan', 'asc');

        if ($this->selectedCategory !== 'all') {
            $query->whereHas('categoryFilm', function ($query) {
                $query->where('slug', $this->selectedCategory);
            });
        }

        return $query->get();
    }

    public function getStandaloneProjectsQuery()
    {
        $query = ModelsProject::query()
            ->with('client')
            ->whereNull('series_id');

        if ($this->selectedCategory !== 'all') {
            $query->whereHas('categoryFilm', function ($query) {
                $query->where('slug', $this->selectedCategory);
            });
        }

        return $query;
    }

    public function getStandaloneProjectsProperty()
    {
        return $this->getStandaloneProjectsQuery()
            ->orderBy('urutan', 'asc')
            ->limit($this->filmLimit)
            ->get();
    }

    public function getFilmsProperty()
    {
        if ($this->selectedSeries) {
            return $this->selectedSeries->episodes()
                ->with('client')
                ->orderBy('urutan', 'asc')
                ->limit($this->filmLimit)
                ->get();
        }

        return $this->standaloneProjects;
    }

    public function getTotalFilmsProperty()
    {
        if ($this->selectedSeries) {
            return $this->selectedSeries->episodes()->count();
        }

        return $this->getStandaloneProjectsQuery()->count();
    }

    public function loadMoreFilm()
    {
        $this->filmLimit = min($this->filmLimit + 6, $this->totalFilms);
    }

    public function render()
    {
        $this->categoryFilm = CategoryFilm::orderBy('urutan', 'asc')->get();

        $seriesList = $this->seriesList;
        $standaloneFilms = $this->standaloneProjects;
        $films = $this->films;
        $totalFilms = $this->totalFilms;

        return view('livewire.project', compact('seriesList', 'standaloneFilms', 'films', 'totalFilms'))
            ->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact,
            ]);
    }
}
