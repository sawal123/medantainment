<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_hash',    // SHA256(ip + app_key) — bukan IP mentah
        'session_id',
        'user_agent',
        'blog_id',
        'is_bot',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
