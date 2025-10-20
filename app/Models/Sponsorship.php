<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sponsorship extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'sponsor_package_id',
        'buyer_id',
        'company_name',
        'company_logo_url',
        'website_url',
        'contact_name',
        'contact_email',
        'contact_phone',
        'status',
        'admin_notes',
        'assets',
        'approved_at',
        'expires_at',
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
            'sponsor_package_id' => 'integer',
            'buyer_id' => 'integer',
            'assets' => 'array',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sponsorPackage(): BelongsTo
    {
        return $this->belongsTo(SponsorPackage::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(SponsorshipFulfillment::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'sponsorship_sport')
            ->withTimestamps();
    }

    /**
     * Check if the sponsorship is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if this sponsorship covers all sports in the event.
     */
    public function coversAllSports(): bool
    {
        $eventSportsCount = $this->event->eventSports()->count();
        $sponsorshipSportsCount = $this->sports()->count();

        return $eventSportsCount > 0 && $eventSportsCount === $sponsorshipSportsCount;
    }

    /**
     * Get formatted list of sport names.
     */
    public function getSportNamesAttribute(): string
    {
        if ($this->coversAllSports()) {
            return 'All Sports';
        }

        return $this->sports->pluck('name')->join(', ');
    }

    /**
     * Scope a query to only include active sponsorships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pending sponsorships.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved sponsorships.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }
}
