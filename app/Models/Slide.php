<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CleansUpMedia;

class Slide extends Model
{
    use HasFactory, CleansUpMedia;

    protected $fillable = [
        'nama',
        'thumbnail',
        'short',
        'link',
        'is_active',
    ];

    protected array $mediaFields = ['thumbnail'];
}
