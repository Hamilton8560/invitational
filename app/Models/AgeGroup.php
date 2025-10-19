<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_sport_id',
        'name',
        'category',
        'min_age',
        'max_age',
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
            'event_sport_id' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function eventSport(): BelongsTo
    {
        return $this->belongsTo(EventSport::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Scope a query to only include youth age groups.
     */
    public function scopeYouth($query)
    {
        return $query->where('category', 'youth');
    }

    /**
     * Scope a query to only include adult age groups.
     */
    public function scopeAdult($query)
    {
        return $query->where('category', 'adult');
    }

    /**
     * Scope a query to only include senior age groups.
     */
    public function scopeSenior($query)
    {
        return $query->where('category', 'senior');
    }

    /**
     * Scope a query to order by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
