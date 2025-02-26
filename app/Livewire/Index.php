<?php

namespace App\Livewire;

use App\Models\Blog;
use App\Models\client;
use App\Models\Landing;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Testimoni;
use Livewire\Component;

class Index extends Component
{
    public $testimoni;
    public $project;
    public $landing;
    public $blog;
    public $client;
    public $setting;
    public $page;
    public function mount(){
        $this->setting = Setting::first();
        $this->page="MEDANTAINMENT";

        $this->client = client::all();
        $this->blog = Blog::all();
        $this->testimoni = Testimoni::all();
        $this->project = Project::all();
        $this->landing = Landing::pluck('value')->toArray();
        // dd($this->landing);
    }
    public function render()
    {
        return view('livewire.index')->layout('components.layouts.app',[
            'page' => $this->page,
            'setting'=> $this->setting
        ]);
    }
}
