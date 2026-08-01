<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CleansUpMedia;

class Photo extends Model
{
    use HasFactory, CleansUpMedia;

    protected $fillable = [
        'client_id',
        'title',
        'photo',
        'urutan',
    ];

    protected array $mediaFields = ['photo'];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
