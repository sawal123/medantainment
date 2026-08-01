<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Carrer;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Candidate;
use App\Models\Internship;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    // carrer_id adalah property readonly dari server — tidak boleh diubah dari luar
    // $carrer_id hanya disimpan untuk keperluan view/render
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

    // === Validation Rules: Lamaran Biasa ===
    // mimetypes: memvalidasi isi file (magic bytes), bukan hanya ekstensi
    protected $rules = [
        'name'         => 'required|string|max:255',
        'email'        => 'required|email|max:255',
        'phone'        => 'required|string|max:15|regex:/^[0-9+\-\s]+$/',
        'resume'       => 'required|file|mimetypes:application/pdf|max:4096',
        'cover_letter' => 'nullable|string|max:5000',
    ];

    public function mount($slug): void
    {
        $this->slug    = $slug;
        $this->setting = Setting::first();
        $this->page    = 'MEDANTAINMENT - Carrer';
        $this->contact = Alamat::first();

        // Null check — cegah Error 500 jika slug tidak ada
        $this->carrer = Carrer::where('slug', $this->slug)->first();

        if (! $this->carrer) {
            session()->flash('error', 'Lowongan tidak ditemukan.');
            redirect()->route('index');
            return;
        }

        $this->title    = $this->carrer->title;
        $this->carrer_id = $this->carrer->id; // read-only reference
    }

    // ===================================================================
    // SIMPAN — Form Internship
    // ===================================================================

    public function simpan(): void
    {
        // [PRIORITAS 4] carrer selalu dari server — jangan percayai input browser
        $carrer = $this->getValidCarrer(expectedType: 'Internship');
        if ($carrer === null) {
            return;
        }

        // [PRIORITAS 6] Validasi dulu, rate limit dikurangi hanya jika valid
        $validated = $this->validate([
            'nama'    => 'required|string|max:255',
            'ttl'     => 'required|string|max:255',
            'alamat'  => 'required|string|max:1000',

            'sekolah_universitas' => 'required|string|max:255',
            'jurusan'             => 'required|string|max:255',
            'periode_magang'      => 'required|string|max:255',

            'keahlian' => 'required|string|max:2000',

            'ketertarikan'          => 'required|array|min:1',
            'ketertarangan_singkat' => 'required|string|max:3000',

            // mimetypes: memvalidasi isi file sebenarnya, bukan hanya ekstensi
            'surat_izin'    => 'nullable|file|mimetypes:application/pdf|max:2048',
            'surat_lamaran' => 'nullable|file|mimetypes:application/pdf|max:2048',
            'cv_portofolio' => 'nullable|file|mimetypes:application/pdf|max:4096',
            'foto_diri'     => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:2048',

            'rating_kreatifitas'     => 'nullable|integer|min:1|max:5',
            'rating_analitis'        => 'nullable|integer|min:1|max:5',
            'rating_komunikasi'      => 'nullable|integer|min:1|max:5',
            'rating_manajemen_waktu' => 'nullable|integer|min:1|max:5',
            'rating_adaptasi'        => 'nullable|integer|min:1|max:5',
            'rating_teamwork'        => 'nullable|integer|min:1|max:5',
            'rating_motivasi'        => 'nullable|integer|min:1|max:5',
            'rating_tekanan'         => 'nullable|integer|min:1|max:5',

            'alasan_internship' => 'required|string|max:3000',
        ]);

        // [PRIORITAS 5] Rate limit — dikurangi SETELAH validasi berhasil
        // Key: hash(ip + session + carrer_id) — tidak menggunakan email mentah
        $rateLimitKey = $this->buildRateLimitKey('internship', $carrer->id);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $this->addError(
                'rate_limit',
                "Terlalu banyak percobaan pengiriman. Coba lagi dalam {$seconds} detik."
            );
            return;
        }

        RateLimiter::hit($rateLimitKey, 600); // blokir 10 menit

        // [PRIORITAS 2] Upload file ke disk private
        DB::transaction(function () use ($carrer, &$validated) {
            if ($this->surat_izin) {
                $validated['surat_izin'] = $this->surat_izin->store(
                    'internship/surat_izin',
                    'private'
                );
            }

            if ($this->surat_lamaran) {
                $validated['surat_lamaran'] = $this->surat_lamaran->store(
                    'internship/surat_lamaran',
                    'private'
                );
            }

            if ($this->cv_portofolio) {
                $validated['cv_portofolio'] = $this->cv_portofolio->store(
                    'internship/cv_portofolio',
                    'private'
                );
            }

            if ($this->foto_diri) {
                $validated['foto_diri'] = $this->foto_diri->store(
                    'internship/foto_diri',
                    'private'
                );
            }

            $validated['ketertarikan'] = $this->ketertarikan;

            // [PRIORITAS 4] carrer_id selalu dari server — bukan dari $validated
            $validated['carrer_id'] = $carrer->id;
            unset($validated['ketertarikan']); // cast sudah di model

            Internship::create(array_merge($validated, [
                'ketertarikan' => $this->ketertarikan,
                'carrer_id'    => $carrer->id,
            ]));
        });

        // [PRIORITAS 6] Reset hanya field form — jangan reset $carrer, $setting, dll
        $this->resetInternshipFields();

        session()->flash('success', 'Form Berhasil Dikirim!');
        $this->dispatch('scroll-to-top');
    }

    // ===================================================================
    // SAVE — Form Lamaran Biasa
    // ===================================================================

    public function save(): void
    {
        // [PRIORITAS 4] carrer selalu dari server
        $carrer = $this->getValidCarrer(expectedType: 'regular');
        if ($carrer === null) {
            return;
        }

        // [PRIORITAS 6] Validasi dulu
        $this->validate();

        // [PRIORITAS 5] Rate limit SETELAH validasi
        $rateLimitKey = $this->buildRateLimitKey('career', $carrer->id);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $this->addError(
                'rate_limit',
                "Terlalu banyak percobaan pengiriman. Coba lagi dalam {$seconds} detik."
            );
            return;
        }

        RateLimiter::hit($rateLimitKey, 600);

        // [PRIORITAS 2] Upload resume ke disk private
        DB::transaction(function () use ($carrer) {
            $resumePath = $this->resume->store('candidates/resumes', 'private');

            // [PRIORITAS 4] carrer_id dari server, bukan dari input user
            Candidate::create([
                'carrer_id'    => $carrer->id,
                'name'         => $this->name,
                'email'        => $this->email,
                'phone'        => $this->phone,
                'resume'       => $resumePath,
                'cover_letter' => $this->cover_letter,
            ]);
        });

        session()->flash('message', 'Lamaran berhasil dikirim!');

        // [PRIORITAS 6] Reset hanya field form, bukan semua state
        $this->resetCareerFields();
    }

    public function render()
    {
        // Null safety check jika lowongan tidak ada
        if (! $this->carrer) {
            return view('livewire.carrer-form')->layout('components.layouts.app', [
                'page'    => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact,
            ]);
        }

        if ($this->carrer->time === 'Internship') {
            return view('livewire.carrer-form-intern')->layout('components.layouts.app', [
                'page'    => $this->page,
                'setting' => $this->setting,
                'contact' => $this->contact,
            ]);
        }

        return view('livewire.carrer-form')->layout('components.layouts.app', [
            'page'    => $this->page,
            'setting' => $this->setting,
            'contact' => $this->contact,
        ]);
    }

    // ===================================================================
    // Helper Methods
    // ===================================================================

    /**
     * Ambil career yang valid dari server berdasarkan slug yang sudah dimount.
     * Validasi: career harus ada, statusnya open, dan tipenya sesuai.
     *
     * @param string $expectedType 'Internship' atau 'regular'
     */
    private function getValidCarrer(string $expectedType): ?Carrer
    {
        // Selalu re-fetch dari DB untuk memastikan status terkini
        $carrer = Carrer::where('slug', $this->slug)->first();

        if (! $carrer) {
            $this->addError('rate_limit', 'Lowongan tidak ditemukan.');
            return null;
        }

        if ($carrer->status !== 'open') {
            $this->addError('rate_limit', 'Pendaftaran untuk lowongan ini sudah ditutup.');
            return null;
        }

        // Validasi tipe form sesuai tipe career — mencegah submit internship ke career biasa
        if ($expectedType === 'Internship' && $carrer->time !== 'Internship') {
            $this->addError('rate_limit', 'Tipe formulir tidak sesuai dengan jenis lowongan.');
            Log::warning('CarrerForm: tipe form tidak sesuai', [
                'slug'          => $this->slug,
                'expected_type' => $expectedType,
                'actual_type'   => $carrer->time,
                'ip'            => request()->ip(),
            ]);
            return null;
        }

        if ($expectedType === 'regular' && $carrer->time === 'Internship') {
            $this->addError('rate_limit', 'Tipe formulir tidak sesuai dengan jenis lowongan.');
            return null;
        }

        return $carrer;
    }

    /**
     * Buat rate limit key yang aman.
     * Menggunakan hash(ip + session_id + carrer_id) agar:
     * - Tidak menyimpan data sensitif (email) di cache key
     * - Setiap lowongan memiliki bucket terpisah
     * - Pengguna dari jaringan yang sama tetap punya bucket berbeda (session)
     */
    private function buildRateLimitKey(string $formType, int $carrerId): string
    {
        $raw = implode('|', [
            $formType,
            $carrerId,
            request()->ip(),
            session()->getId(),
        ]);

        return $formType . '-form:' . hash('sha256', $raw);
    }

    /**
     * [PRIORITAS 6] Reset hanya field form internship.
     * Tidak mereset $carrer, $setting, $page, $contact, $slug, $carrer_id.
     */
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

    /**
     * [PRIORITAS 6] Reset hanya field form lamaran biasa.
     */
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
