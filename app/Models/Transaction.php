<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'related_transaction_id',
        'category_id',
        'amount',
        'transaction_type',
        'description',
        'currency',
        'is_manual',
        'is_read_only',
        'external_reference',
        'attachment_url',
        'created_by_source',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_manual' => 'boolean',
        'is_read_only' => 'boolean',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account for this transaction
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the category for this transaction
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    /**
     * Get the related transaction (for transfers between accounts)
     */
    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id')->withDefault();
    }

    /**
     * Get all attachments for this transaction
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class);
    }

    /**
     * Scope: Get only income transactions
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('transaction_type', 'income');
    }

    /**
     * Scope: Get only expense transactions
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('transaction_type', 'expense');
    }

    /**
     * Scope: Get transactions for a specific period
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get recent transactions
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('transaction_date', '>=', now()->subDays($days));
    }

    /**
     * Scope: Get transactions for a specific user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessor: Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Accessor: Get display amount (positive for income, negative for expense)
     */
    public function getDisplayAmountAttribute(): float
    {
        return $this->transaction_type === 'income' ? $this->amount : -$this->amount;
    }
}
