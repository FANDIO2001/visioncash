<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'notification_id',
        'channel_id',
        'status',
        'error_message',
        'external_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the notification for this log
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(UserNotification::class, 'notification_id');
    }

    /**
     * Get the notification channel for this log
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }
}
