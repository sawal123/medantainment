<?php

namespace App\Models;

use App\Rules\SafeNavigationUrl;
use App\Traits\CleansUpMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Slide extends Model
{
    use CleansUpMedia, HasFactory;

    protected $fillable = [
        'nama',
        'thumbnail',
        'short',
        'link',
        'is_active',
    ];

    protected array $mediaFields = ['thumbnail'];

    /**
     * Mutator mutakhir untuk menyaring link slide saat disimpan.
     */
    protected function link(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value === null || trim((string) $value) === '') {
                    return null;
                }

                $normalized = SafeNavigationUrl::normalize((string) $value);

                if ($normalized === null) {
                    throw ValidationException::withMessages([
                        'link' => 'URL slide tidak aman atau tidak valid.',
                    ]);
                }

                return $normalized;
            }
        );
    }

    /**
     * Accessor aman untuk mengembalikan URL yang valid (mencegah XSS meskipun data lama di-bypass di DB).
     */
    public function getSafeLinkAttribute(): ?string
    {
        $rawLink = $this->getRawOriginal('link');

        return SafeNavigationUrl::normalize($rawLink);
    }
}
