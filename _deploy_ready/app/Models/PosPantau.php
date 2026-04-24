<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosPantau extends Model
{
    use HasFactory;

    protected $table = 'pos_pantau';

    protected $fillable = [
        'kode',
        'nama',
        'kabupaten',
        'alamat',
        'latitude',
        'longitude',
        'radius_meter',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_meter' => 'integer',
        ];
    }

    /* ─── Relations ─── */

    public function petugas(): HasMany
    {
        return $this->hasMany(User::class, 'pos_pantau_id');
    }

    public function spaRecords(): HasMany
    {
        return $this->hasMany(Spa::class, 'pos_pantau_id');
    }

    /* ─── Scopes ─── */

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /* ─── Helpers ─── */

    /**
     * Hitung jarak titik GPS ke lokasi pos (dalam meter) — Haversine formula.
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6_371_000; // meter

        $latFrom = deg2rad((float) $this->latitude);
        $lngFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad($lat);
        $lngTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadius * asin(sqrt($a));
    }

    /**
     * Cek apakah titik GPS berada dalam radius pos.
     */
    public function isWithinRadius(float $lat, float $lng): bool
    {
        return $this->distanceTo($lat, $lng) <= $this->radius_meter;
    }
}
