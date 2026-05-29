<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'api_endpoint',
        'webhook_secret',
        'description',
        'icon_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all integrations for this provider
     */
    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }
}
