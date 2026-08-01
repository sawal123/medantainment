<?php

namespace App\Livewire;

use App\Models\Alamat;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class BlogDetail extends Component
{
    public $setting;

    public $page;

    public $slug;

    public $post;

    public $category;

    public $recent;

    public $serupa;

    public $contact;

    public $query;

    // Window dedup: satu pengunjung tidak dihitung ulang dalam 30 menit
    private const DEDUP_WINDOW_MINUTES = 30;

    // Pola User-Agent bot sederhana
    private const BOT_PATTERNS = [
        'bot', 'crawler', 'spider', 'scraper',
        'curl', 'wget', 'python', 'java/', 'go-http-client',
        'headless', 'phantomjs', 'lighthouse',
    ];

    public function search()
    {
        if (! empty($this->query)) {
            return redirect()->to('/blog/'.urlencode($this->query));
        }
    }

    public function mount($slug)
    {
        $this->setting = Setting::first();
        $this->page = 'MEDANTAINMENT - Blog';
        $this->contact = Alamat::first();
        $this->slug = $slug;
        $this->post = Blog::published()->where('slug', $slug)->firstOrFail();
        $this->serupa = Blog::published()
            ->where('category_id', $this->post->category->id)
            ->where('id', '!=', $this->post->id)
            ->take(2)
            ->get();
        $this->recent = Blog::published()->latest()->take(3)->get();
        $this->category = Category::all();

        // Rekam statistik kunjungan dengan deduplikasi
        $this->recordVisit($this->post->id);
    }

    public function render()
    {
        return view('livewire.blog-detail')->layout('components.layouts.app', [
            'page' => $this->page,
            'setting' => $this->setting,
            'contact' => $this->contact,
            'meta_title' => $this->post->seo_title,
            'meta_description' => $this->post->seo_description,
            'meta_image' => $this->post->image,
        ]);
    }

    // ───────────────────────────────────────────────
    // Visit Recording dengan Dedup
    // ───────────────────────────────────────────────

    private function recordVisit(int $blogId): void
    {
        $userAgent = request()->userAgent() ?? '';

        // Lewati bot berdasarkan User-Agent sederhana
        if ($this->isBot($userAgent)) {
            return;
        }

        $sessionId = session()->getId();
        $ipAddress = request()->ip() ?? '0.0.0.0';

        // Hash IP dengan app key — tidak menyimpan IP mentah
        $ipHash = hash('sha256', $ipAddress.config('app.key'));

        // Dedup cache key: satu pengunjung (session + blog) per window
        $dedupKey = 'visitor_dedup:'.hash('sha256', $sessionId.$blogId);

        // Jika sudah dihitung dalam window ini, lewati
        if (Cache::has($dedupKey)) {
            return;
        }

        // Cek di DB juga (untuk ketahanan jika cache di-flush)
        $alreadyRecorded = Visitor::where('session_id', $sessionId)
            ->where('blog_id', $blogId)
            ->where('created_at', '>=', now()->subMinutes(self::DEDUP_WINDOW_MINUTES))
            ->exists();

        if ($alreadyRecorded) {
            // Set cache agar tidak query DB terus
            Cache::put($dedupKey, true, now()->addMinutes(self::DEDUP_WINDOW_MINUTES));

            return;
        }

        Visitor::create([
            'ip_hash' => $ipHash,
            'session_id' => $sessionId,
            'user_agent' => mb_substr($userAgent, 0, 500), // batasi panjang
            'blog_id' => $blogId,
        ]);

        // Set cache dedup
        Cache::put($dedupKey, true, now()->addMinutes(self::DEDUP_WINDOW_MINUTES));
    }

    /**
     * Deteksi bot sederhana berdasarkan User-Agent.
     * Ini bukan perlindungan penuh, tetapi mengurangi noise signifikan.
     */
    private function isBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $lowerAgent = strtolower($userAgent);
        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($lowerAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
