<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'channel_id',
        'transaction_received',
        'budget_80_percent',
        'budget_100_percent',
        'daily_summary',
        'monthly_summary',
        'suspicious_transaction',
        'subscription_alerts',
    ];

    protected $casts = [
        'transaction_received' => 'boolean',
        'budget_80_percent' => 'boolean',
        'budget_100_percent' => 'boolean',
        'daily_summary' => 'boolean',
        'monthly_summary' => 'boolean',
        'suspicious_transaction' => 'boolean',
        'subscription_alerts' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user for this notification preference
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification channel for this preference
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }
}
