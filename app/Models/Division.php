<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sport_id',
        'event_sport_id',
        'event_time_slot_id',
        'age_group_id',
        'skill_level_id',
        'name',
        'gender',
        'team_size',
        'max_teams',
        'max_players',
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
            'sport_id' => 'integer',
            'event_sport_id' => 'integer',
            'event_time_slot_id' => 'integer',
            'age_group_id' => 'integer',
            'skill_level_id' => 'integer',
            'team_size' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function eventSport(): BelongsTo
    {
        return $this->belongsTo(EventSport::class);
    }

    public function eventTimeSlot(): BelongsTo
    {
        return $this->belongsTo(EventTimeSlot::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function skillLevel(): BelongsTo
    {
        return $this->belongsTo(SkillLevel::class);
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

    /**
     * Scope a query to only include youth divisions.
     */
    public function scopeYouth($query)
    {
        return $query->whereHas('ageGroup', function ($q) {
            $q->where('category', 'youth');
        });
    }

    /**
     * Scope a query to only include adult divisions.
     */
    public function scopeAdult($query)
    {
        return $query->whereHas('ageGroup', function ($q) {
            $q->where('category', 'adult');
        });
    }

    /**
     * Scope a query to only include senior divisions.
     */
    public function scopeSenior($query)
    {
        return $query->whereHas('ageGroup', function ($q) {
            $q->where('category', 'senior');
        });
    }

    /**
     * Scope a query to filter by sport.
     */
    public function scopeBySport($query, $sportId)
    {
        return $query->where('sport_id', $sportId);
    }

    /**
     * Scope a query to order by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
