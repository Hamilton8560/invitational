<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsorPackageBenefit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sponsor_package_id',
        'benefit_type',
        'name',
        'description',
        'quantity',
        'is_enabled',
        'requires_asset_upload',
        'display_order',
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
            'sponsor_package_id' => 'integer',
            'quantity' => 'integer',
            'is_enabled' => 'boolean',
            'requires_asset_upload' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function sponsorPackage(): BelongsTo
    {
        return $this->belongsTo(SponsorPackage::class);
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(SponsorshipFulfillment::class);
    }

    /**
     * Scope a query to only include enabled benefits.
     */
    public function scopeEnabled($query)
    {
        return $query->whereRaw('is_enabled = true');
    }

    /**
     * Scope a query to order by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
