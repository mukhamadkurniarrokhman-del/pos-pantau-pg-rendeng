<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FotoMuatan extends Model
{
    use HasFactory;

    protected $table = 'foto_muatan';

    protected $fillable = [
        'spa_id',
        'jenis',
        'path',
        'url_thumbnail',
        'size_kb',
        'mime_type',
        'width',
        'height',
        'hash_sha256',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'size_kb' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'captured_at' => 'datetime',
        ];
    }

    protected $appends = ['url_full'];

    /* ─── Relations ─── */

    public function spa(): BelongsTo
    {
        return $this->belongsTo(Spa::class, 'spa_id');
    }

    /* ─── Accessors ─── */

    public function getUrlFullAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }
}
