<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Carrer as ModelsCarrer;
use App\Models\Setting;
use Livewire\Component;

class Carrer extends Component
{
    public $setting;
    public $page;
    public $carrer;
    public $contact;
    public function mount()
    {
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Carrer";
        $this->contact = Alamat::first();
        $this->carrer = ModelsCarrer::all();
    }
    public function render()
    {
        return view('livewire.carrer')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact'=>$this->contact
        ]);
    }
}
