<?php

namespace App\Models;

use App\Rules\SafeVideoEmbedUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_type',
        'title',
    ];

    /**
     * Accessor untuk mendapatkan embed URL video yang aman jika hero_type adalah hero4.
     */
    public function getEmbedUrlAttribute(): string
    {
        if ($this->hero_type === 'hero4' && ! empty($this->title)) {
            return SafeVideoEmbedUrl::toEmbedUrl($this->title);
        }

        return '';
    }
}
