<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'vision',
        'mission_title',
        'mission',
        'image',
        'highlights',
        'video_url',
    ];

    protected $casts = [
        'highlights' => 'array',
    ];

    protected array $mediaFields = ['image'];
}
