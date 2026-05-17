<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Carrer;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Candidate;
use App\Models\Internship;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\RateLimiter;

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

    // ✅ [SECURITY FIX #1 & #6] Validasi diperkuat:
    // - mimetypes: (cek konten file sebenarnya, bukan hanya ekstensi)
    // - Hanya PDF yang diizinkan untuk resume (hapus doc/docx — rawan macro virus)
    // - Tambah regex validasi phone agar hanya angka/tanda +
    // - Batasi max length cover_letter agar tidak bisa diisi payload besar
    protected $rules = [
        'name'         => 'required|string|max:255',
        'email'        => 'required|email|max:255',
        'phone'        => 'required|string|max:15|regex:/^[0-9+\-\s]+$/',
        'resume'       => 'required|file|mimetypes:application/pdf|max:2048',
        'cover_letter' => 'nullable|string|max:5000',
    ];
    public function mount($slug)
    {
        $this->slug    = $slug;
        $this->setting = Setting::first();
        $this->page    = "MEDANTAINMENT - Carrer";
        $this->contact = Alamat::first();

        // ✅ [SECURITY FIX #5] Null check — cegah Error 500 jika slug tidak ada sama sekali
        $this->carrer = Carrer::where('slug', $this->slug)->first();

        if (!$this->carrer) {
            session()->flash('error', 'Lowongan tidak ditemukan.');
            return redirect()->route('index'); // ✅ Menggunakan nama route home yang benar ('index')
        }

        $this->title    = $this->carrer->title;
        $this->carrer_id = $this->carrer->id;
    }

    // ==========================
    public function simpan()
    {
        // ✅ [SECURITY FIX] Cegah pengiriman jika lowongan sudah ditutup
        if ($this->carrer->status !== 'open') {
            $this->addError('rate_limit', 'Pendaftaran untuk lowongan ini sudah ditutup.');
            return;
        }
        // ✅ [SECURITY FIX #3] Rate Limiting — maks 3 submit per IP per 10 menit
        $key = 'internship-form:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('rate_limit', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
            return;
        }
        RateLimiter::hit($key, 600); // blokir 600 detik = 10 menit

        // ✅ [SECURITY FIX #1] Validasi file menggunakan mimetypes: (cek konten sesungguhnya)
        // Hapus doc/docx — format Word dapat mengandung macro virus berbahaya
        $validated = $this->validate([
            'carrer_id' => 'required|integer|exists:carrers,id',

            'nama'    => 'required|string|max:255',
            'ttl'     => 'required|string|max:255',
            'alamat'  => 'required|string|max:1000',

            'sekolah_universitas' => 'required|string|max:255',
            'jurusan'             => 'required|string|max:255',
            'periode_magang'      => 'required|string|max:255',

            'keahlian' => 'required|string|max:2000',

            'ketertarikan'         => 'required|array|min:1',
            'ketertarangan_singkat' => 'required|string|max:3000',

            // ✅ mimetypes: memvalidasi isi file, bukan hanya ekstensi
            'surat_izin'    => 'nullable|file|mimetypes:application/pdf,image/jpeg,image/png|max:2048',
            'surat_lamaran' => 'nullable|file|mimetypes:application/pdf,image/jpeg,image/png|max:2048',
            'cv_portofolio' => 'nullable|file|mimetypes:application/pdf,image/jpeg,image/png|max:4096',
            'foto_diri'     => 'nullable|file|mimetypes:image/jpeg,image/png|max:2048',

            'rating_kreatifitas'    => 'nullable|integer|min:1|max:5',
            'rating_analitis'       => 'nullable|integer|min:1|max:5',
            'rating_komunikasi'     => 'nullable|integer|min:1|max:5',
            'rating_manajemen_waktu'=> 'nullable|integer|min:1|max:5',
            'rating_adaptasi'       => 'nullable|integer|min:1|max:5',
            'rating_teamwork'       => 'nullable|integer|min:1|max:5',
            'rating_motivasi'       => 'nullable|integer|min:1|max:5',
            'rating_tekanan'        => 'nullable|integer|min:1|max:5',

            'alasan_internship' => 'required|string|max:3000',
        ]);

        // ✅ FILE UPLOAD — Livewire store() otomatis menggunakan nama acak (UUID)
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

        $validated['ketertarikan'] = $this->ketertarikan;

        Internship::create($validated);

        $this->resetExcept(['carrer', 'carrer_id', 'page', 'setting', 'contact']);

        session()->flash('success', 'Form Berhasil Dikirim!');
        $this->dispatch('scroll-to-top');
    }



    // ===================
    // ===================

    public function save()
    {
        // ✅ [SECURITY FIX] Cegah pengiriman jika lowongan sudah ditutup
        if ($this->carrer->status !== 'open') {
            $this->addError('rate_limit', 'Pendaftaran untuk lowongan ini sudah ditutup.');
            return;
        }
        // ✅ [SECURITY FIX #3] Rate Limiting — maks 3 submit per IP per 10 menit
        $key = 'career-form:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('rate_limit', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
            return;
        }
        RateLimiter::hit($key, 600); // blokir 600 detik = 10 menit

        // ✅ [SECURITY FIX #1 & #6] Menggunakan $rules yang sudah diperkuat (mimetypes, regex phone)
        $this->validate();

        // ✅ Livewire store() menggunakan nama UUID otomatis — aman
        $resumePath = $this->resume->store('resumes', 'public');

        // ✅ [SECURITY FIX #2] Explicit field assignment — tidak ada mass assignment dari input user
        Candidate::create([
            'carrer_id'    => $this->carrer->id, // dari server, bukan dari input user
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'resume'       => $resumePath,
            'cover_letter' => $this->cover_letter,
        ]);

        session()->flash('message', 'Lamaran berhasil dikirim!');
        $this->resetExcept('carrer_id');
    }

    public function render()
    {
        // ✅ Null safety check jika lowongan ditutup/null
        if (!$this->carrer) {
            return view('livewire.carrer-form')->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact
            ]);
        }

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
