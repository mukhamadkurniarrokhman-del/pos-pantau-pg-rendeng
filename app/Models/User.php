<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nip',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'pos_pantau_id',
        'is_active',
        'last_login_at',
        'last_ping_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'last_ping_at' => 'datetime',
        ];
    }

    /* ─── Relations ─── */

    public function pos(): BelongsTo
    {
        return $this->belongsTo(PosPantau::class, 'pos_pantau_id');
    }

    public function spaRecords(): HasMany
    {
        return $this->hasMany(Spa::class, 'petugas_id');
    }

    /* ─── Scopes ─── */

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePetugas($query)
    {
        return $query->where('role', 'petugas_pos');
    }

    /* ─── Helpers ─── */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas_pos';
    }

    /** Admin atau Supervisor boleh lihat dashboard & data lintas pos */
    public function canViewDashboard(): bool
    {
        return $this->isAdmin() || $this->isSupervisor();
    }
}
