<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'billing_cycle',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'stripe_subscription_id',
        'cinetpay_subscription_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user for this subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan for this subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get all invoices for this subscription
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all payment methods for this subscription
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Scope: Get only active subscriptions
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Get subscriptions in trial period
     */
    public function scopeOnTrial(Builder $query): Builder
    {
        return $query->where('status', 'trialing')
                     ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope: Get subscriptions with past due status
     */
    public function scopePastDue(Builder $query): Builder
    {
        return $query->where('status', 'past_due');
    }

    /**
     * Scope: Get expired subscriptions
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope: Get subscriptions by user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessor: Check if subscription is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Accessor: Check if subscription is in trial
     */
    public function getIsOnTrialAttribute(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at > now();
    }

    /**
     * Accessor: Get days remaining in trial
     */
    public function getDaysRemainingInTrialAttribute(): ?int
    {
        if (!$this->is_on_trial) {
            return null;
        }
        return $this->trial_ends_at->diffInDays(now());
    }

    /**
     * Accessor: Get days until renewal
     */
    public function getDaysUntilRenewalAttribute(): int
    {
        return $this->current_period_end->diffInDays(now());
    }
}
