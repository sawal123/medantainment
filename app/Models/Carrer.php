<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Carrer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'salary',
        'status',
        'time',
        'apply_link',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($career) {
            $career->slug = Str::slug($career->title);
            $career->apply_link = url("/career/form/{$career->slug}");
        });

        static::updating(function ($career) {
            $career->slug = Str::slug($career->title);
            $career->apply_link = url("/career/form/{$career->slug}");
        });
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function internship()
    {
        return $this->hasMany(Internship::class);
    }
}
