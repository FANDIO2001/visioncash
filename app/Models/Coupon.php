<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'max_uses',
        'current_uses',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all plans for this coupon
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'coupon_plans');
    }

    /**
     * Get all invoices for this coupon
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
