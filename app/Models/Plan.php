<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price_monthly_xaf',
        'price_annual_xaf',
        'max_accounts',
        'max_transactions_month',
        'max_categories',
        'max_budgets',
        'max_integrations',
        'export_pdf',
        'export_excel',
        'csv_import',
        'recurring_transactions',
        'forecast',
        'history_months',
        'is_active',
    ];

    protected $casts = [
        'price_monthly_xaf' => 'decimal:2',
        'price_annual_xaf' => 'decimal:2',
        'export_pdf' => 'boolean',
        'export_excel' => 'boolean',
        'csv_import' => 'boolean',
        'recurring_transactions' => 'boolean',
        'forecast' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all subscriptions for this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get all coupons for this plan
     */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_plans');
    }
}
