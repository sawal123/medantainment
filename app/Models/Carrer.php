<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carrer extends Model
{
    use HasFactory;
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($career) {
            $career->slug = Str::slug($career->title);
            $career->apply_link = url("/form/{$career->slug}");
        });

        static::updating(function ($career) {
            $career->slug = Str::slug($career->title);
            $career->apply_link = url("/carrer/form/{$career->slug}");
        });
    }
    public function candidates()
    {
        return $this->hasMany(\App\Models\Candidate::class);
    }
    public function internship()
    {
        return $this->hasMany(\App\Models\Internship::class);
    }
}
