<?php

namespace App\Livewire;

use App\Models\Carrer;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Candidate;
use Livewire\WithFileUploads;

class CarrerForm extends Component
{
    use WithFileUploads;
    public $setting;
    public $page;
    public $slug;
    public $carrer;
    public $title;

    public $carrer_id, $name, $email, $phone, $resume, $cover_letter;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'required|string|max:15',
        'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
        'cover_letter' => 'nullable|string',
    ];
    public function mount($slug)
    {
        $this->slug = $slug;
        $this->setting = Setting::first();
        $this->page = "MEDANTAINMENT - Carrer";


        $this->carrer = Carrer::where('slug', $this->slug)->where('status', 'open')->first();
        $this->title = $this->carrer->title;
    }
    public function save()
    {
        $this->validate();

        $resumePath = $this->resume->store('resumes', 'public');

        Candidate::create([
            'carrer_id' => $this->carrer->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume' => $resumePath,
            'cover_letter' => $this->cover_letter,
        ]);

        session()->flash('message', 'Lamaran berhasil dikirim!');
        $this->reset();
    }
    public function render()
    {
        return view('livewire.carrer-form')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting
        ]);
    }
}
