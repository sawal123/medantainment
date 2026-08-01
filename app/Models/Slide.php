<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'nama',
        'thumbnail',
        'short',
        'link',
        'is_active',
    ];

    protected array $mediaFields = ['thumbnail'];
}
