<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'slug', 'description', 'seo_title', 'seo_description', 'og_image'
    ];

    // Slug dan SEO otomatis dibuat jika kosong
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            if (empty($category->seo_title)) {
                $category->seo_title = \Illuminate\Support\Str::limit($category->name, 60, '');
            }
            if (empty($category->seo_description)) {
                $plainDescription = strip_tags($category->description ?? '');
                $plainDescription = html_entity_decode($plainDescription);
                $plainDescription = preg_replace('/\s+/', ' ', $plainDescription);
                $category->seo_description = \Illuminate\Support\Str::limit(trim($plainDescription), 160, '...');
            }
        });
    }

}
