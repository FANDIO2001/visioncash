<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Integration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'user_id',
        'account_id',
        'external_account_id',
        'external_account_name',
        'token_expires_at',
        'last_sync_at',
        'next_sync_at',
        'sync_status',
        'last_error',
        'is_active',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'next_sync_at' => 'datetime',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    public function setOAuthTokens(string $accessToken, string $refreshToken, ?\DateTimeInterface $expiresAt = null): void
    {
        $this->access_token = $accessToken;
        $this->refresh_token = $refreshToken;
        $this->token_expires_at = $expiresAt;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByProvider(Builder $query, int $providerId): Builder
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNeedingSync(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_sync_at')
                    ->orWhere('next_sync_at', '<=', now());
            });
    }

    public function scopeInError(Builder $query): Builder
    {
        return $query->where('sync_status', 'error');
    }

    public function getIsTokenExpiredAttribute(): bool
    {
        return $this->token_expires_at && $this->token_expires_at <= now();
    }

    public function getSecondsUntilNextSyncAttribute(): ?int
    {
        if (! $this->next_sync_at) {
            return null;
        }

        return (int) now()->diffInSeconds($this->next_sync_at, false);
    }
}
