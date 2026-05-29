<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationChannel extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'display_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this notification channel
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all notification logs for this channel
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'channel_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'channel_id');
    }
}
