<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Carrer;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Candidate;
use App\Models\Internship;
use Livewire\WithFileUploads;

class CarrerForm extends Component
{
    use WithFileUploads;
    public $setting;
    public $page;
    public $slug;
    public $carrer = null;
    public $title;
    public $contact;

    public $carrer_id, $name, $email, $phone, $resume, $cover_letter;

    // ========================

    public $nama;
    public $ttl;
    public $alamat;

    public $sekolah_universitas;
    public $jurusan;
    public $periode_magang;

    public $keahlian;

    public $ketertarikan = []; // checkbox → array
    public $ketertarangan_singkat;

    public $surat_izin;
    public $surat_lamaran;
    public $cv_portofolio;
    public $foto_diri;

    // Rating
    public $rating_kreatifitas;
    public $rating_analitis;
    public $rating_komunikasi;
    public $rating_manajemen_waktu;
    public $rating_adaptasi;
    public $rating_teamwork;
    public $rating_motivasi;
    public $rating_tekanan;

    public $alasan_internship;

    // ========================

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
        $this->contact = Alamat::first();

        $this->carrer = Carrer::where('slug', $this->slug)->where('status', 'open')->first();
        // dd($this->carrer);
        $this->title = $this->carrer->title;
        $this->carrer_id = $this->carrer->id;
    }

    // ==========================
    public function simpan()
    {
        $validated = $this->validate([
            'carrer_id' => 'required|integer|exists:carrers,id',

            'nama' => 'required|string|max:255',
            'ttl' => 'required|string|max:255',
            'alamat' => 'required|string',

            'sekolah_universitas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'periode_magang' => 'required|string|max:255',

            'keahlian' => 'required|string',

            'ketertarikan' => 'required|array|min:1',
            'ketertarangan_singkat' => 'required|string',

            'surat_izin' => 'nullable|file|mimes:pdf,jpg,png,doc,docx|max:2048',
            'surat_lamaran' => 'nullable|file|mimes:pdf,jpg,png,doc,docx|max:2048',
            'cv_portofolio' => 'nullable|file|mimes:pdf,jpg,png,doc,docx|max:4096',
            'foto_diri' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',

            'rating_kreatifitas' => 'nullable|integer|min:1|max:5',
            'rating_analitis' => 'nullable|integer|min:1|max:5',
            'rating_komunikasi' => 'nullable|integer|min:1|max:5',
            'rating_manajemen_waktu' => 'nullable|integer|min:1|max:5',
            'rating_adaptasi' => 'nullable|integer|min:1|max:5',
            'rating_teamwork' => 'nullable|integer|min:1|max:5',
            'rating_motivasi' => 'nullable|integer|min:1|max:5',
            'rating_tekanan' => 'nullable|integer|min:1|max:5',

            'alasan_internship' => 'required|string',
        ]);

        // FILE UPLOAD (AMAN)
        if ($this->surat_izin) {
            $validated['surat_izin'] = $this->surat_izin->store('internship/surat_izin', 'public');
        }

        if ($this->surat_lamaran) {
            $validated['surat_lamaran'] = $this->surat_lamaran->store('internship/surat_lamaran', 'public');
        }

        if ($this->cv_portofolio) {
            $validated['cv_portofolio'] = $this->cv_portofolio->store('internship/cv_portofolio', 'public');
        }

        if ($this->foto_diri) {
            $validated['foto_diri'] = $this->foto_diri->store('internship/foto_diri', 'public');
        }

        // Simpan JSON otomatis
        $validated['ketertarikan'] = $this->ketertarikan;

        Internship::create($validated);

        // Tetap simpan carrer_id
        $this->resetExcept(['carrer', 'carrer_id', 'page', 'setting', 'contact']);


        session()->flash('success', 'Form Berhasil Dikirim!');
        $this->dispatch('scroll-to-top');
    }



    // ===================
    // ===================

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
        $this->resetExcept('carrer_id');
    }

    public function render()
    {
        if ($this->carrer->time == 'Internship') {
            return view('livewire.carrer-form-intern')->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact
            ]);
        } else {
            return view('livewire.carrer-form')->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact
            ]);
        }
    }
}
