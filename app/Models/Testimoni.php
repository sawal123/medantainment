<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CleansUpMedia;

class Testimoni extends Model
{
    use HasFactory, CleansUpMedia;

    protected $fillable = [
        'name',
        'position',
        'message',
        'photo',
    ];

    protected array $mediaFields = ['photo'];
}
