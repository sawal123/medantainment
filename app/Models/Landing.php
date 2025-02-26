<?php

namespace App\Models;

use Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Landing extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    // public function setValueAttribute($value)
    // {
    //     \Log::info('Data yang masuk ke mutator:', ['value' => $value]);
    //     $this->attributes['value'] = $this->convertToEmbed($value);
    // }

    // private function convertToEmbed($url)
    // {
    //     if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
    //         preg_match('/(youtu\.be\/|v=|\/embed\/|\/v\/|\/watch\?v=)([^&]+)/', $url, $matches);
    //         return isset($matches[2]) ? "https://www.youtube.com/embed/{$matches[2]}" : $url;
    //     }
    //     return $url;
    // }
}
