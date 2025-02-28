<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Blog as ModelsBlog;

class Blog extends Component
{
    public $setting;
    public $page;
    public $blog;
    public $cekBlog;

    public $blogLimit = 6;
    #[Url]
    public $search = null;
    public function loadData()
    {
        $this->blog = ModelsBlog::take($this->blogLimit)->get();
    }
    public function loadMoreBlog()
    {
        $this->blogLimit += 6;
        $this->loadData();
    }
    public function mount($search = null)
    {
        $this->search = $search ? urldecode($search) : null;
        // dd($this->search);

        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Blog";

        $this->loadData();
        $this->cekBlog = ModelsBlog::all();
    }
    public function render()
    {
        $blogSearch = ModelsBlog::where('title', 'like', "%{$this->search}%")
            ->orWhereHas('category', function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->get();

        return view('livewire.blog', ['blogSearch' => $blogSearch])->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting
        ]);
    }
}
