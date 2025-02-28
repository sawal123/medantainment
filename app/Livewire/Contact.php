<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Setting;
use Livewire\Component;

class Contact extends Component
{
    public $setting;
    public $page;
    public $alamat;

    public function mount()
    {
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Carrer";

        $this->alamat = Alamat::first();
    }
    public function render()
    {
        return view('livewire.contact')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting
        ]);
    }
}
