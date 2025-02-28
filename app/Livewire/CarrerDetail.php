<?php

namespace App\Livewire;

use App\Models\Carrer;
use App\Models\Setting;
use Livewire\Component;

class CarrerDetail extends Component
{

    public $setting;
    public $page;
    public $carrer;
    public $carrers;
    public $slug;
    public function mount($slug)
    {
        $this->slug = $slug;
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Carrer";

        $this->carrer = Carrer::where('slug', $this->slug)->first();
        $this->carrers = Carrer::take(3)->get();
        // dd($this->carrer->description);
    }
    public function render()
    {
        return view('livewire.carrer-detail')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting
        ]);
    }
}
