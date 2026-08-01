<?php

namespace App\Models;

use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use CleansUpMedia, HasFactory;

    protected array $mediaFields = ['logo', 'favicon'];

    protected string $mediaDisk = 'public';

    protected $guarded = [];
}
