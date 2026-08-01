<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoLanding extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    protected array $mediaFields = ['value'];
}
