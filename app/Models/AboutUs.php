<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CleansUpMedia;

class AboutUs extends Model
{
    use HasFactory, CleansUpMedia;

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
