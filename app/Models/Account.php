<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_type_id',
        'integration_id',
        'account_number',
        'account_name',
        'is_active',
        'currency',
        'color',
        'iban',
        'initial_balance',
        'balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'initial_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns this account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account type for this account
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the integration for this account
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class)->withDefault();
    }

    /**
     * Get all transactions for this account
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all account balance histories for this account
     */
    public function balanceHistories(): HasMany
    {
        return $this->hasMany(AccountBalanceHistory::class);
    }

    /**
     * Get all recurring transactions for this account
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Get all CSV imports for this account
     */
    public function csvImports(): HasMany
    {
        return $this->hasMany(CsvImport::class);
    }

    /**
     * Scope: Get only active accounts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get accounts for a specific user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get accounts with balance below a threshold
     */
    public function scopeOverdrawn(Builder $query): Builder
    {
        return $query->whereRaw('balance < 0');
    }

    /**
     * Accessor: Check if account is overdrawn
     */
    public function getIsOverdrawnAttribute(): bool
    {
        return $this->balance < 0;
    }

    /**
     * Accessor: Get formatted balance with currency
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }
}
