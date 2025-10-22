<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsorPackage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'tier',
        'name',
        'description',
        'price',
        'max_quantity',
        'current_quantity',
        'is_active',
        'is_template',
        'display_order',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_environment',
        'last_synced_at',
        'paypal_product_id',
        'paypal_environment',
        'paypal_last_synced_at',
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
            'event_id' => 'integer',
            'price' => 'decimal:2',
            'max_quantity' => 'integer',
            'current_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_template' => 'boolean',
            'display_order' => 'integer',
            'last_synced_at' => 'datetime',
            'paypal_last_synced_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(SponsorPackageBenefit::class);
    }

    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }

    /**
     * Get the number of spots remaining for this package.
     */
    public function getSpotsRemainingAttribute(): int
    {
        if ($this->max_quantity === null) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_quantity - $this->current_quantity);
    }

    /**
     * Check if the package has spots available.
     */
    public function hasAvailableSpots(): bool
    {
        return $this->max_quantity === null || $this->current_quantity < $this->max_quantity;
    }

    /**
     * Scope a query to only include active packages.
     */
    public function scopeActive($query)
    {
        return $query->whereRaw('is_active = true');
    }

    /**
     * Scope a query to only include template packages.
     */
    public function scopeTemplates($query)
    {
        return $query->whereRaw('is_template = true');
    }

    /**
     * Scope a query to order by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Check if package needs syncing to Stripe
     */
    public function needsStripeSync(): bool
    {
        $currentEnv = config('stripe.environment');

        return $this->stripe_product_id === null
            || $this->stripe_price_id === null
            || $this->stripe_environment !== $currentEnv
            || ($this->last_synced_at && $this->updated_at > $this->last_synced_at);
    }

    /**
     * Check if package needs syncing to PayPal
     */
    public function needsPayPalSync(): bool
    {
        $currentEnv = config('paypal.environment');

        return $this->paypal_product_id === null
            || $this->paypal_environment !== $currentEnv
            || ($this->paypal_last_synced_at && $this->updated_at > $this->paypal_last_synced_at);
    }
}
