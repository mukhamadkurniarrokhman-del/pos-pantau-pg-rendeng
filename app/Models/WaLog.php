<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaLog extends Model
{
    use HasFactory;

    protected $table = 'wa_log';

    protected $fillable = [
        'spa_id',
        'target_phone',
        'target_name',
        'message',
        'status',
        'fonnte_message_id',
        'fonnte_response',
        'retry_count',
        'sent_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'retry_count' => 'integer',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /* ─── Relations ─── */

    public function spa(): BelongsTo
    {
        return $this->belongsTo(Spa::class, 'spa_id');
    }

    /* ─── Scopes ─── */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    /* ─── Helpers ─── */

    public function canRetry(): bool
    {
        return $this->retry_count < 3 && in_array($this->status, ['pending', 'failed']);
    }
}
