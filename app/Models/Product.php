<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'event_time_slot_id',
        'type',
        'category',
        'sport_name',
        'name',
        'description',
        'price',
        'cash_prize',
        'format',
        'max_quantity',
        'current_quantity',
        'division_id',
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
            'event_time_slot_id' => 'integer',
            'price' => 'decimal:2',
            'cash_prize' => 'decimal:2',
            'division_id' => 'integer',
            'display_order' => 'integer',
            'last_synced_at' => 'datetime',
            'paypal_last_synced_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eventTimeSlot(): BelongsTo
    {
        return $this->belongsTo(EventTimeSlot::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the number of spots remaining for this product.
     */
    public function getSpotsRemainingAttribute(): int
    {
        if ($this->max_quantity === null) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_quantity - $this->current_quantity);
    }

    /**
     * Check if the product has spots available.
     */
    public function hasAvailableSpots(): bool
    {
        return $this->max_quantity === null || $this->current_quantity < $this->max_quantity;
    }

    /**
     * Scope a query to only include youth products.
     */
    public function scopeYouth($query)
    {
        return $query->where('category', 'youth');
    }

    /**
     * Scope a query to only include adult products.
     */
    public function scopeAdult($query)
    {
        return $query->where('category', 'adult');
    }

    /**
     * Scope a query to only include senior products.
     */
    public function scopeSenior($query)
    {
        return $query->where('category', 'senior');
    }

    /**
     * Scope a query to only include registrations.
     */
    public function scopeRegistrations($query)
    {
        return $query->whereIn('type', ['individual_registration', 'team_registration']);
    }

    /**
     * Scope a query to only include spectator tickets.
     */
    public function scopeSpectatorTickets($query)
    {
        return $query->where('type', 'spectator_ticket');
    }

    /**
     * Scope a query to only include advertising products.
     */
    public function scopeAdvertising($query)
    {
        return $query->where('type', 'advertising');
    }

    /**
     * Scope a query to filter by sport name.
     */
    public function scopeBySport($query, $sportName)
    {
        return $query->where('sport_name', $sportName);
    }

    /**
     * Scope a query to order by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Check if product needs syncing to Stripe
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
     * Get Stripe product name
     */
    public function getStripeProductName(): string
    {
        $eventName = $this->event ? $this->event->name.' - ' : '';

        return $eventName.$this->name;
    }

    /**
     * Get Stripe product description
     */
    public function getStripeProductDescription(): string
    {
        $desc = $this->description ?? $this->name;

        if ($this->event) {
            $desc .= "\n\nEvent: {$this->event->name}";
            $desc .= "\nDates: {$this->event->start_date->format('M j')} - {$this->event->end_date->format('M j, Y')}";
        }

        if ($this->division) {
            $desc .= "\nDivision: {$this->division->name}";
        }

        return $desc;
    }

    /**
     * Check if product needs syncing to PayPal
     */
    public function needsPayPalSync(): bool
    {
        $currentEnv = config('paypal.environment');

        return $this->paypal_product_id === null
            || $this->paypal_environment !== $currentEnv
            || ($this->paypal_last_synced_at && $this->updated_at > $this->paypal_last_synced_at);
    }

    /**
     * Get PayPal product name
     */
    public function getPayPalProductName(): string
    {
        $eventName = $this->event ? $this->event->name.' - ' : '';

        return $eventName.$this->name;
    }

    /**
     * Get PayPal product description
     */
    public function getPayPalProductDescription(): string
    {
        $desc = $this->description ?? $this->name;

        if ($this->event) {
            $desc .= "\n\nEvent: {$this->event->name}";
            $desc .= "\nDates: {$this->event->start_date->format('M j')} - {$this->event->end_date->format('M j, Y')}";
        }

        if ($this->division) {
            $desc .= "\nDivision: {$this->division->name}";
        }

        return $desc;
    }
}
