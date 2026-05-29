<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SyncLog extends Model
{
    protected $fillable = [
        'integration_id',
        'status',
        'transactions_fetched',
        'transactions_imported',
        'duplicates_skipped',
        'error_message',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the integration for this sync log
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Scope: Get successful syncs
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Get failed syncs
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get in progress syncs
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: Get recent syncs
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * Accessor: Get sync duration in seconds
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->ended_at) {
            return null;
        }
        return $this->ended_at->diffInSeconds($this->started_at);
    }

    /**
     * Accessor: Get import success rate
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->transactions_fetched === 0) {
            return 0;
        }
        return ($this->transactions_imported / $this->transactions_fetched) * 100;
    }
}
