<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'amount',
        'reason',
        'status',
        'paddle_refund_id',
        'requested_by',
        'requested_at',
        'processed_at',
        'processed_by',
        'requested_by_id',
        'processed_by_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'sale_id' => 'integer',
            'amount' => 'decimal:2',
            'requested_by' => 'integer',
            'requested_at' => 'timestamp',
            'processed_at' => 'timestamp',
            'processed_by' => 'integer',
            'requested_by_id' => 'integer',
            'processed_by_id' => 'integer',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
