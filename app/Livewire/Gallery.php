<?php

namespace App\Livewire;

use App\Models\Photo;
use App\Models\Setting;
use Livewire\Component;

class Gallery extends Component
{
    public $photoLimit = 6;
    public $setting;
    public $page;
    public function mount()
    {
        error_reporting(0);
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT";
    }


    public function getPhotosProperty()
    {
        return Photo::limit($this->photoLimit)->get();
    }

    public function loadMore()
    {
        $this->photoLimit += 6;
    }

    public function render()
    {
        return view('livewire.gallery')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,

        ]);
    }
}
