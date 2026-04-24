<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kebun extends Model
{
    use HasFactory;

    protected $table = 'kebun';

    protected $fillable = [
        'kode_kebun',
        'nama',
        'petani_id',
        'luas_hektar',
        'desa',
        'kecamatan',
        'kabupaten',
        'latitude',
        'longitude',
        'nomor_blok',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'luas_hektar' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /* ─── Relations ─── */

    public function petani(): BelongsTo
    {
        return $this->belongsTo(Petani::class, 'petani_id');
    }

    public function kontrak(): HasMany
    {
        return $this->hasMany(Kontrak::class, 'kebun_id');
    }
}
