<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Petani extends Model
{
    use HasFactory;

    protected $table = 'petani';

    protected $fillable = [
        'kode_petani',
        'nama',
        'nik',
        'no_wa',
        'alamat',
        'desa',
        'kecamatan',
        'kabupaten',
        'kelompok_tani',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /* ─── Relations ─── */

    public function kebun(): HasMany
    {
        return $this->hasMany(Kebun::class, 'petani_id');
    }

    public function kontrak(): HasMany
    {
        return $this->hasMany(Kontrak::class, 'petani_id');
    }

    /* ─── Scopes ─── */

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /* ─── Helpers ─── */

    /**
     * Normalisasi nomor WA ke format internasional (62xxxx).
     */
    public function getWaInternationalAttribute(): ?string
    {
        if (! $this->no_wa) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $this->no_wa);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
