<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class client extends Model
{
    use HasFactory;
    protected $guarded =[];
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($project) {
            if ($project->logo) {
                Storage::disk('public')->delete($project->logo);
            }
        });
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    public function photos()
    {
        return $this->hasMany(Project::class);
    }


    
}
