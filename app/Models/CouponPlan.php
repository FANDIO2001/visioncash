<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CouponPlan extends Pivot
{
    protected $table = 'coupon_plans';

    protected $fillable = [
        'coupon_id',
        'plan_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the coupon for this coupon plan
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the plan for this coupon plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
