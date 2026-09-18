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

    public $selectedCategory = 'all';

    public $firstCategory = '';

    public function mount($slug = null)
    {
        $this->setting = Setting::first();
        $this->page = 'MEDANTAINMENT - Project';
        $this->contact = Alamat::first();

        if (request()->routeIs('project.series.show')) {
            $this->selectedSeries = ProjectSeries::query()
                ->where('is_active', true)
                ->where('slug', $slug)
                ->firstOrFail();
            $this->selectedCategory = 'all';
            $this->firstCategory = $this->selectedSeries;

            return;
        }

        if (request()->routeIs('project.category.show')) {
            $category = CategoryFilm::query()
                ->where('slug', $slug)
                ->firstOrFail();

            $this->selectedCategory = $category->slug;
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
        $this->firstCategory = $slug === 'all'
            ? ''
            : CategoryFilm::where('slug', $slug)->firstOrFail();
        $this->dispatch('change-url', slug: $slug);
    }

    public function getSeriesListProperty()
    {
        return $this->getSeriesQuery()
            ->orderBy('urutan', 'asc')
            ->get();
    }

    public function getStandaloneProjectsQuery()
    {
        $query = ModelsProject::query()
            ->with(['client', 'categoryFilm'])
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

    public function getMixedItemsProperty()
    {
        if ($this->selectedSeries) {
            return collect();
        }

        $projects = $this->getStandaloneProjectsQuery()
            ->get()
            ->map(fn (ModelsProject $project) => [
                'content_type' => 'standalone_project',
                'id' => $project->id,
                'category_urutan' => $project->categoryFilm?->urutan ?? PHP_INT_MAX,
                'category_id' => $project->category_film_id ?? PHP_INT_MAX,
                'urutan' => $project->urutan,
                'model' => $project,
            ]);

        $series = $this->getSeriesQuery()
            ->get()
            ->map(fn (ProjectSeries $series) => [
                'content_type' => 'project_series',
                'id' => $series->id,
                'category_urutan' => $series->categoryFilm?->urutan ?? PHP_INT_MAX,
                'category_id' => $series->category_film_id ?? PHP_INT_MAX,
                'urutan' => $series->urutan,
                'model' => $series,
            ]);

        return $projects
            ->concat($series)
            ->sort(function (array $a, array $b) {
                $catUrutanA = $a['category_urutan'] ?? PHP_INT_MAX;
                $catUrutanB = $b['category_urutan'] ?? PHP_INT_MAX;
                if ($catUrutanA !== $catUrutanB) {
                    return $catUrutanA <=> $catUrutanB;
                }

                $catIdA = $a['category_id'] ?? PHP_INT_MAX;
                $catIdB = $b['category_id'] ?? PHP_INT_MAX;
                if ($catIdA !== $catIdB) {
                    return $catIdA <=> $catIdB;
                }

                $itemUrutanA = $a['urutan'] ?? 0;
                $itemUrutanB = $b['urutan'] ?? 0;
                if ($itemUrutanA !== $itemUrutanB) {
                    return $itemUrutanA <=> $itemUrutanB;
                }

                $typeCmp = strcmp($a['content_type'], $b['content_type']);
                if ($typeCmp !== 0) {
                    return $typeCmp;
                }

                return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
            })
            ->take($this->filmLimit)
            ->values();
    }

    public function getFilmsProperty()
    {
        if ($this->selectedSeries) {
            return $this->selectedSeries->episodes()
                ->with(['client', 'categoryFilm'])
                ->orderBy('urutan', 'asc')
                ->limit($this->filmLimit)
                ->get();
        }

        return collect();
    }

    public function getTotalFilmsProperty()
    {
        if ($this->selectedSeries) {
            return $this->selectedSeries->episodes()->count();
        }

        return $this->getStandaloneProjectsQuery()->count()
            + $this->getSeriesQuery()->count();
    }

    public function loadMoreFilm()
    {
        $this->filmLimit = min($this->filmLimit + 6, $this->totalFilms);
    }

    public function render()
    {
        $this->categoryFilm = CategoryFilm::orderBy('urutan', 'asc')->get();

        $mixedItems = $this->mixedItems;
        $films = $this->films;
        $totalFilms = $this->totalFilms;

        return view('livewire.project', compact('mixedItems', 'films', 'totalFilms'))
            ->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact,
            ]);
    }

    private function getSeriesQuery()
    {
        $query = ProjectSeries::query()
            ->where('is_active', true)
            ->with('categoryFilm')
            ->withCount('episodes');

        if ($this->selectedCategory !== 'all') {
            $query->whereHas('categoryFilm', function ($query) {
                $query->where('slug', $this->selectedCategory);
            });
        }

        return $query;
    }
}
