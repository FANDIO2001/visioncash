<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'access_token_hash',
        'refresh_token_hash',
        'expires_at',
        'ip_address',
        'user_agent',
        'revoked',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('revoked', false)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function revoke(): void
    {
        $this->update([
            'revoked' => true,
            'revoked_at' => now(),
        ]);
    }
}
