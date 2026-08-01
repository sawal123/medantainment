<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'name',
        'position',
        'message',
        'photo',
    ];

    protected array $mediaFields = ['photo'];
}
