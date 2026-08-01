<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'photo',
        'urutan',
    ];

    protected array $mediaFields = ['photo'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
