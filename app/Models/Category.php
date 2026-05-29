<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'description',
        'icon',
        'color',
        'category_type',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this category
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this category
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all budgets for this category
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get all recurring transactions for this category
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Scope: Get only active categories
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get default categories
     */
    public function scopeDefaults(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Get income categories
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('category_type', 'income');
    }

    /**
     * Scope: Get expense categories
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('category_type', 'expense');
    }

    /**
     * Scope: Get categories for user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
