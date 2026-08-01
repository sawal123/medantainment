<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'nama',
        'posisi',
        'gambar',
        'urutan',
    ];

    protected array $mediaFields = ['gambar'];
}
