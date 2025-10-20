<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorshipFulfillment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sponsorship_id',
        'sponsor_package_benefit_id',
        'status',
        'notes',
        'proof_files',
        'completed_at',
        'completed_by',
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
            'sponsorship_id' => 'integer',
            'sponsor_package_benefit_id' => 'integer',
            'proof_files' => 'array',
            'completed_at' => 'datetime',
            'completed_by' => 'integer',
        ];
    }

    public function sponsorship(): BelongsTo
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(SponsorPackageBenefit::class, 'sponsor_package_benefit_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Check if the fulfillment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->completed_at !== null;
    }

    /**
     * Scope a query to only include completed fulfillments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending fulfillments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in progress fulfillments.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
