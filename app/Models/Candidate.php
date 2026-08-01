<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use CleansUpMedia, HasFactory;

    protected string $mediaDisk = 'private';

    protected array $mediaFields = ['resume', 'cover_letter'];

    protected $fillable = [
        'carrer_id',
        'name',
        'email',
        'phone',
        'resume',
        'cover_letter',
        'status',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function carrer()
    {
        return $this->belongsTo(Carrer::class);
    }
}
