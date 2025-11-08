<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use App\Models\Team as ModelsTeam;

class Team extends Component
{
    public $setting;
    public $page;
    public $team;
    public function mount()
    {
        $this->team = ModelsTeam::all();
        error_reporting(0);
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT";
        // dd($this->team);
    }
    public function render()
    {
        return view('livewire.team')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
        ]);
    }
}
