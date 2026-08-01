<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class Blog extends Model
{
    use CleansUpMedia, HasFactory;

    protected array $mediaFields = ['image', 'og_image'];

    protected string $mediaDisk = 'public';

    protected $fillable = [
        'user_id', 'title', 'slug', 'content', 'image', 'category_id', 'status',
        'published_at', 'seo_title', 'seo_description', 'og_image',
    ];

    // Slug, sanitasi XSS, dan SEO otomatis diset jika kosong/disimpan
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
            if (Auth::check() && empty($blog->user_id)) {
                $blog->user_id = Auth::id();
            }

            // Sanitasi HTML konten dengan profil 'blog'
            if (! empty($blog->content)) {
                $blog->content = Purifier::clean($blog->content, 'blog');
            }

            // Otomasi SEO jika kosong
            if (empty($blog->seo_title)) {
                $blog->seo_title = Str::limit($blog->title, 60, '');
            }
            if (empty($blog->seo_description)) {
                $plainContent = strip_tags($blog->content ?? '');
                $plainContent = html_entity_decode($plainContent);
                $plainContent = preg_replace('/\s+/', ' ', $plainContent);
                $blog->seo_description = Str::limit(trim($plainContent), 160, '...');
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'published')
                ->orWhere(function ($sq) {
                    $sq->where('status', 'scheduled')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
                });
        });
    }
}
