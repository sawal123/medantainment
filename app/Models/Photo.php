<?php

namespace App\Models;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Photo extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            if ($photo->photo) {
                Storage::disk('public')->delete($photo->photo);
            }
        });
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
