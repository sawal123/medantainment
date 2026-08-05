<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Carrer;
use App\Models\Setting;
use App\Services\CareerApplicationService;
use App\Services\CareerSubmissionRateLimiter;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
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

    public $carrer_id;

    // === Form Fields: Lamaran Biasa ===
    public $name;

    public $email;

    public $phone;

    public $resume;

    public $cover_letter;

    // === Form Fields: Internship ===
    public $nama;

    public $ttl;

    public $alamat;

    public $sekolah_universitas;

    public $jurusan;

    public $periode_magang;

    public $keahlian;

    public $ketertarikan = [];

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

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:15|regex:/^[0-9+\-\s]+$/',
        'resume' => 'required|file|mimetypes:application/pdf|max:4096',
        'cover_letter' => 'nullable|string|max:5000',
    ];

    public function mount($slug): void
    {
        $this->slug = $slug;
        $this->setting = Setting::first();
        $this->page = 'MEDANTAINMENT - Carrer';
        $this->contact = Alamat::first();

        $this->carrer = Carrer::where('slug', $this->slug)->first();

        if (! $this->carrer) {
            session()->flash('error', 'Lowongan tidak ditemukan.');
            redirect()->route('index');

            return;
        }

        $this->title = $this->carrer->title;
        $this->carrer_id = $this->carrer->id;
    }

    public function simpan(
        CareerApplicationService $service,
        CareerSubmissionRateLimiter $rateLimiter
    ): void {
        $carrer = $this->getValidCarrer(expectedType: 'Internship');
        if ($carrer === null) {
            return;
        }

        $validated = $this->validate([
            'nama' => 'required|string|max:255',
            'ttl' => 'required|string|max:255',
            'alamat' => 'required|string|max:1000',
            'sekolah_universitas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'periode_magang' => 'required|string|max:255',
            'keahlian' => 'required|string|max:2000',
            'ketertarikan' => 'required|array|min:1',
            'ketertarangan_singkat' => 'required|string|max:3000',
            'surat_izin' => 'nullable|file|mimetypes:application/pdf|max:2048',
            'surat_lamaran' => 'nullable|file|mimetypes:application/pdf|max:2048',
            'cv_portofolio' => 'nullable|file|mimetypes:application/pdf|max:4096',
            'foto_diri' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:2048',
            'rating_kreatifitas' => 'nullable|integer|min:1|max:5',
            'rating_analitis' => 'nullable|integer|min:1|max:5',
            'rating_komunikasi' => 'nullable|integer|min:1|max:5',
            'rating_manajemen_waktu' => 'nullable|integer|min:1|max:5',
            'rating_adaptasi' => 'nullable|integer|min:1|max:5',
            'rating_teamwork' => 'nullable|integer|min:1|max:5',
            'rating_motivasi' => 'nullable|integer|min:1|max:5',
            'rating_tekanan' => 'nullable|integer|min:1|max:5',
            'alasan_internship' => 'required|string|max:3000',
        ]);

        $reservation = $rateLimiter->reserve('internship', $carrer->id, request()->ip(), session()->getId());

        if (! $reservation) {
            $seconds = $rateLimiter->availableIn('internship', $carrer->id, request()->ip(), session()->getId());
            $this->addError(
                'rate_limit',
                "Terlalu banyak percobaan pengiriman. Coba lagi sekitar {$seconds} detik."
            );

            return;
        }

        $files = [
            'surat_izin' => $this->surat_izin,
            'surat_lamaran' => $this->surat_lamaran,
            'cv_portofolio' => $this->cv_portofolio,
            'foto_diri' => $this->foto_diri,
        ];

        try {
            $service->submitInternship($carrer, $validated, $files);
            $rateLimiter->commit($reservation);
        } catch (\Throwable $e) {
            $rateLimiter->rollback($reservation);

            throw $e;
        }

        $this->resetInternshipFields();

        session()->flash('success', 'Form Berhasil Dikirim!');
        $this->dispatch('scroll-to-top');
    }

    public function save(
        CareerApplicationService $service,
        CareerSubmissionRateLimiter $rateLimiter
    ): void {
        $carrer = $this->getValidCarrer(expectedType: 'regular');
        if ($carrer === null) {
            return;
        }

        $this->validate();

        $reservation = $rateLimiter->reserve('career', $carrer->id, request()->ip(), session()->getId());

        if (! $reservation) {
            $seconds = $rateLimiter->availableIn('career', $carrer->id, request()->ip(), session()->getId());
            $this->addError(
                'rate_limit',
                "Terlalu banyak percobaan pengiriman. Coba lagi sekitar {$seconds} detik."
            );

            return;
        }

        try {
            $service->submitCandidate($carrer, [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'cover_letter' => $this->cover_letter,
            ], $this->resume);
            $rateLimiter->commit($reservation);
        } catch (\Throwable $e) {
            $rateLimiter->rollback($reservation);

            throw $e;
        }

        session()->flash('message', 'Lamaran berhasil dikirim!');

        $this->resetCareerFields();
    }

    public function render()
    {
        if (! $this->carrer) {
            return view('livewire.carrer-form')->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact,
            ]);
        }

        if ($this->carrer->time === 'Internship') {
            return view('livewire.carrer-form-intern')->layout('components.layouts.app', [
                'page' => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact,
            ]);
        }

        return view('livewire.carrer-form')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact' => $this->contact,
        ]);
    }

    private function getValidCarrer(string $expectedType): ?Carrer
    {
        $carrer = Carrer::where('slug', $this->slug)->first();

        if (! $carrer) {
            $this->addError('rate_limit', 'Lowongan tidak ditemukan.');

            return null;
        }

        if ($carrer->status !== 'open') {
            $this->addError('rate_limit', 'Pendaftaran untuk lowongan ini sudah ditutup.');

            return null;
        }

        if ($expectedType === 'Internship' && $carrer->time !== 'Internship') {
            $this->addError('rate_limit', 'Tipe formulir tidak sesuai dengan jenis lowongan.');
            Log::warning('CarrerForm: tipe form tidak sesuai', [
                'slug' => $this->slug,
                'expected_type' => $expectedType,
                'actual_type' => $carrer->time,
                'ip' => request()->ip(),
            ]);

            return null;
        }

        if ($expectedType === 'regular' && $carrer->time === 'Internship') {
            $this->addError('rate_limit', 'Tipe formulir tidak sesuai dengan jenis lowongan.');

            return null;
        }

        return $carrer;
    }

    private function resetInternshipFields(): void
    {
        $this->reset([
            'nama',
            'ttl',
            'alamat',
            'sekolah_universitas',
            'jurusan',
            'periode_magang',
            'keahlian',
            'ketertarikan',
            'ketertarangan_singkat',
            'surat_izin',
            'surat_lamaran',
            'cv_portofolio',
            'foto_diri',
            'rating_kreatifitas',
            'rating_analitis',
            'rating_komunikasi',
            'rating_manajemen_waktu',
            'rating_adaptasi',
            'rating_teamwork',
            'rating_motivasi',
            'rating_tekanan',
            'alasan_internship',
        ]);
    }

    private function resetCareerFields(): void
    {
        $this->reset([
            'name',
            'email',
            'phone',
            'resume',
            'cover_letter',
        ]);
    }
}
