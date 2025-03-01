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
        $this->post = Blog::where('slug', $slug)->firstOrFail();
        $this->serupa = Blog::where('category_id', $this->post->category->id)->take(2)->get();
        // dd($this->serupa);
        $this->recent = Blog::latest()->take(3)->get();

        $this->category = Category::all();
    }
    public function render()
    {
        return view('livewire.blog-detail')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact'=>$this->contact
        ]);
    }
}
