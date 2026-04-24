<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kontrak extends Model
{
    use HasFactory;

    protected $table = 'kontrak';

    protected $fillable = [
        'nomor_kontrak',
        'petani_id',
        'kebun_id',
        'musim_giling',
        'tanggal_kontrak',
        'tanggal_mulai_panen',
        'tanggal_akhir_panen',
        'estimasi_tonase',
        'harga_per_ton',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kontrak' => 'date',
            'tanggal_mulai_panen' => 'date',
            'tanggal_akhir_panen' => 'date',
            'estimasi_tonase' => 'decimal:2',
            'harga_per_ton' => 'decimal:2',
        ];
    }

    /* ─── Relations ─── */

    public function petani(): BelongsTo
    {
        return $this->belongsTo(Petani::class, 'petani_id');
    }

    public function kebun(): BelongsTo
    {
        return $this->belongsTo(Kebun::class, 'kebun_id');
    }

    public function spaRecords(): HasMany
    {
        return $this->hasMany(Spa::class, 'kontrak_id');
    }

    /* ─── Scopes ─── */

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByMusim($query, string $musim)
    {
        return $query->where('musim_giling', $musim);
    }

    /* ─── Helpers ─── */

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}
