<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetHistory extends Model
{
    protected $table = 'budget_history';

    protected $fillable = [
        'budget_id',
        'spent',
        'realization_rate',
        'period_month',
        'recorded_at',
    ];

    protected $casts = [
        'spent' => 'decimal:2',
        'realization_rate' => 'decimal:2',
        'period_month' => 'date',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the budget for this history
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }
}
