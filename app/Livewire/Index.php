<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Blog;
use App\Models\client;
use App\Models\Landing;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Sosmed;
use App\Models\Team;
use App\Models\Testimoni;
use Livewire\Component;

error_reporting(0);
class Index extends Component
{
    public $testimoni;
    public $project;
    public $landing;
    public $blog;
    public $client;
    public $setting;
    public $page;
    public $contact;
    public $sosmed;
    public $team;
    public function mount(){
        $this->setting = Setting::first();
        $this->page="MEDANTAINMENT";
        $this->team = Team::all();
        $this->client = client::all();
        $this->blog = Blog::all();
        $this->testimoni = Testimoni::all();
        $this->project = Project::all();
        $this->contact = Alamat::first();
        $this->sosmed = Sosmed::all();
        $this->landing = Landing::pluck('value')->toArray();
        // dd($this->landing);
    }
    public function render()
    {
        return view('livewire.index')->layout('components.layouts.app',[
            'page' => $this->page,
            'setting'=> $this->setting,
            'contact'=>$this->contact
        ]);
    }
}
