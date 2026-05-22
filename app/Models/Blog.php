<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Blog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'title', 'slug', 'content', 'image', 'category_id', 'status',
        'published_at', 'seo_title', 'seo_description', 'og_image'
    ];

    // Slug dan SEO otomatis dibuat jika kosong
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

    // Relasi ke Kategori
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

    // Scope untuk memfilter artikel yang sudah terbit
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
