<?php

namespace App\Livewire;

use App\Models\Blog;
use App\Models\Alamat;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Category;

class BlogDetail extends Component
{
    public $setting;
    public $page;
    public $slug;
    public $post;
    public $category;

    public $recent;
    public $serupa;
    public $contact;

    public $query;

    public function search()
    {
        if (!empty($this->query)) {
            return redirect()->to('/blog/' . urlencode($this->query));
        }
    }
    public function mount($slug)
    {

        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Blog";
        $this->contact = Alamat::first();
        $this->slug = $slug;
        $this->post = Blog::published()->where('slug', $slug)->firstOrFail();
        $this->serupa = Blog::published()->where('category_id', $this->post->category->id)->where('id', '!=', $this->post->id)->take(2)->get();
        // dd($this->serupa);
        $this->recent = Blog::published()->latest()->take(3)->get();

        $this->category = Category::all();

        // Rekam statistik kunjungan artikel
        \App\Models\Visitor::create([
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'user_agent' => request()->userAgent(),
            'blog_id' => $this->post->id,
        ]);
    }
    public function render()
    {
        return view('livewire.blog-detail')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact' => $this->contact,
            'meta_title' => $this->post->seo_title,
            'meta_description' => $this->post->seo_description,
            'meta_image' => $this->post->image, // Ambil dari thumbnail/gambar utama artikel
        ]);
    }
}
