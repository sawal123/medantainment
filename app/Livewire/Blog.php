<?php

namespace App\Livewire;

use App\Models\Alamat;
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
    public $contact;

    public $blogLimit = 6;
    #[Url]
    public $search = null;
    public function loadData()
    {
        $this->blog = ModelsBlog::published()->take($this->blogLimit)->get();
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
        $this->contact = Alamat::first();
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Blog";

        $this->loadData();
        $this->cekBlog = ModelsBlog::published()->get();

        // Rekam statistik kunjungan umum
        \App\Models\Visitor::create([
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'user_agent' => request()->userAgent(),
            'blog_id' => null,
        ]);
    }
    public function render()
    {
        $blogSearch = ModelsBlog::published()
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhereHas('category', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->get();

        return view('livewire.blog', ['blogSearch' => $blogSearch])->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact'=>$this->contact
        ]);
    }
}
