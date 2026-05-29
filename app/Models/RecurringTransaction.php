<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class RecurringTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'amount',
        'frequency_day',
        'frequency_weekday',
        'start_date',
        'end_date',
        'next_occurrence',
        'is_active',
        'description',
        'frequency',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_occurrence' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns this recurring transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account for this recurring transaction
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the category for this recurring transaction
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: Get only active recurring transactions
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get transactions due soon
     */
    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->where('is_active', true)
                     ->whereBetween('next_occurrence', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    /**
     * Scope: Get expired recurring transactions
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('end_date', '<', now()->toDateString());
    }

    /**
     * Scope: Get by frequency
     */
    public function scopeByFrequency(Builder $query, string $frequency): Builder
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope: Get for user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessor: Check if transaction is due today
     */
    public function getIsDueTodayAttribute(): bool
    {
        return $this->next_occurrence && $this->next_occurrence->isToday();
    }

    /**
     * Accessor: Check if transaction is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date < now()->toDateString();
    }

    /**
     * Accessor: Get days until next occurrence
     */
    public function getDaysUntilNextAttribute(): ?int
    {
        if (!$this->next_occurrence) {
            return null;
        }
        return $this->next_occurrence->diffInDays(now());
    }
}
