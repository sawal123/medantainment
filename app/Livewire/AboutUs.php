<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Setting;
use Livewire\Component;

class AboutUs extends Component
{
    public $setting;
    public $page;
    public $data;
    public $contact;
    public function mount()
    {
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT-About Us";
        $this->data = \App\Models\AboutUs::first(); // ambil 1 data
        $this->contact = Alamat::first();
    }
    public function render()
    {

        return view('livewire.about-us')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact' => $this->contact,
        ]);
    }
}
