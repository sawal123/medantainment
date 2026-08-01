<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CleansUpMedia;

class Team extends Model
{
    use HasFactory, CleansUpMedia;

    protected $fillable = [
        'nama',
        'posisi',
        'gambar',
        'urutan',
    ];

    protected array $mediaFields = ['gambar'];
}
