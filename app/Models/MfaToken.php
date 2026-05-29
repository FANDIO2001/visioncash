<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MfaToken extends Model
{
    protected $fillable = [
        'user_id',
        'mfa_type',
        'secret',
        'backup_codes',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this MFA token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
