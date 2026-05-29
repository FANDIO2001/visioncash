<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountBalanceHistory extends Model
{
    protected $table = 'account_balance_history';

    protected $fillable = [
        'account_id',
        'balance',
        'recorded_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the account for this balance history
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
