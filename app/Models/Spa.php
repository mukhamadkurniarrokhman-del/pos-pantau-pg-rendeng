<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spa extends Model
{
    use HasFactory;

    protected $table = 'spa';

    protected $fillable = [
        'nomor_spa',
        'pos_pantau_id',
        'tanggal_spa',
        'urutan',
        'nomor_polisi',
        'nama_sopir',
        'kontrak_id',
        'snapshot_nomor_kontrak',
        'snapshot_nama_petani',
        'snapshot_nama_kebun',
        'gps_latitude',
        'gps_longitude',
        'gps_accuracy_meters',
        'distance_to_pos_meters',
        'gps_valid',
        'is_mock_location',
        'gps_metadata',
        'status',
        'rejection_reason',
        'petugas_id',
        'waktu_pemantauan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_spa' => 'date',
            'urutan' => 'integer',
            'gps_latitude' => 'decimal:7',
            'gps_longitude' => 'decimal:7',
            'gps_accuracy_meters' => 'decimal:2',
            'distance_to_pos_meters' => 'decimal:2',
            'gps_valid' => 'boolean',
            'is_mock_location' => 'boolean',
            'gps_metadata' => 'array',
            'waktu_pemantauan' => 'datetime',
        ];
    }

    /* ─── Relations ─── */

    public function pos(): BelongsTo
    {
        return $this->belongsTo(PosPantau::class, 'pos_pantau_id');
    }

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(Kontrak::class, 'kontrak_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function fotoMuatan(): HasMany
    {
        return $this->hasMany(FotoMuatan::class, 'spa_id');
    }

    public function waLog(): HasMany
    {
        return $this->hasMany(WaLog::class, 'spa_id');
    }

    /* ─── Scopes ─── */

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal_spa', today());
    }

    public function scopeByPos($query, int $posId)
    {
        return $query->where('pos_pantau_id', $posId);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /* ─── Helpers ─── */

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasFakeGps(): bool
    {
        return $this->is_mock_location === true;
    }
}
