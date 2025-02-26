<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['blog_id', 'comment', 'is_approved'];

    // Relasi ke Blog
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
