<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Budget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'spent',
        'period_type',
        'start_date',
        'alert_threshold_percentage',
        'alert_sent_80',
        'alert_sent_100',
        'is_active',
        'end_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'alert_sent_80' => 'boolean',
        'alert_sent_100' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns this budget
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this budget
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all budget histories for this budget
     */
    public function histories(): HasMany
    {
        return $this->hasMany(BudgetHistory::class);
    }

    /**
     * Scope: Get only active budgets
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get budgets for current period
     */
    public function scopeCurrentPeriod(Builder $query): Builder
    {
        return $query->where('start_date', '<=', now()->toDateString())
                     ->where('end_date', '>=', now()->toDateString());
    }

    /**
     * Scope: Get budgets by user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get budgets that exceeded 80% threshold
     */
    public function scopeExceededThreshold(Builder $query): Builder
    {
        return $query->whereRaw('(spent / amount * 100) >= alert_threshold_percentage');
    }

    /**
     * Accessor: Get percentage spent
     */
    public function getPercentageSpentAttribute(): float
    {
        return $this->amount > 0 ? ($this->spent / $this->amount) * 100 : 0;
    }

    /**
     * Accessor: Check if budget is exceeded
     */
    public function getIsExceededAttribute(): bool
    {
        return $this->spent >= $this->amount;
    }

    /**
     * Accessor: Get remaining amount
     */
    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->spent);
    }
}
