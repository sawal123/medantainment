<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CleansUpMedia;

class PhotoLanding extends Model
{
    use HasFactory, CleansUpMedia;

    protected $fillable = [
        'key',
        'value',
    ];

    protected array $mediaFields = ['value'];
}
