<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class CsvImport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'file_name',
        'file_path',
        'column_mapping',
        'status',
        'total_rows',
        'imported_rows',
        'failed_rows',
        'error_message',
        'error_details',
        'completed_at',
    ];

    protected $casts = [
        'column_mapping' => 'json',
        'error_details' => 'json',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns this CSV import
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account for this CSV import
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope: Get completed imports
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Get failed imports
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get pending imports
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get processing imports
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    /**
     * Accessor: Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return ($this->imported_rows / $this->total_rows) * 100;
    }

    /**
     * Accessor: Get failure rate percentage
     */
    public function getFailureRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return ($this->failed_rows / $this->total_rows) * 100;
    }
}
