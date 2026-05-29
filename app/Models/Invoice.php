<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount',
        'currency',
        'status',
        'invoice_date',
        'paid_at',
        'pdf_url',
        'stripe_invoice_id',
        'coupon_id',
        'discount_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user for this invoice
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription for this invoice
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the coupon for this invoice
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class)->withDefault();
    }

    /**
     * Scope: Get paid invoices
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: Get pending invoices
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get invoices for a specific user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get invoices between dates
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get overdue invoices
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'paid')
                     ->where('invoice_date', '<', now()->subDays(30)->toDateString());
    }

    /**
     * Accessor: Get total amount (with discount applied)
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->amount - $this->discount_amount;
    }

    /**
     * Accessor: Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Accessor: Check if invoice is paid
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Accessor: Check if invoice is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->invoice_date < now()->subDays(30)->toDateString();
    }
}
