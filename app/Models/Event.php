<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'venue_id',
        'name',
        'slug',
        'start_date',
        'end_date',
        'status',
        'refund_cutoff_date',
    ];

    /**
     * Boot the model and automatically generate slug from name.
     */
    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);
            }
        });

        static::updating(function (Event $event) {
            if ($event->isDirty('name') && empty($event->slug)) {
                $event->slug = Str::slug($event->name);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'venue_id' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'refund_cutoff_date' => 'date',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function eventTimeSlots(): HasMany
    {
        return $this->hasMany(EventTimeSlot::class);
    }

    public function eventSports(): HasMany
    {
        return $this->hasMany(EventSport::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function individualPlayers(): HasMany
    {
        return $this->hasMany(IndividualPlayer::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function booths(): HasMany
    {
        return $this->hasMany(Booth::class);
    }

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class);
    }

    public function websiteAds(): HasMany
    {
        return $this->hasMany(WebsiteAd::class);
    }

    public function sponsorPackages(): HasMany
    {
        return $this->hasMany(SponsorPackage::class);
    }

    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }
}
