<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'sport_id',
        'time_slot_id',
        'space_required_sqft',
        'max_teams',
        'max_players',
        'event_time_slot_id',
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
            'sport_id' => 'integer',
            'time_slot_id' => 'integer',
            'event_time_slot_id' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function eventTimeSlot(): BelongsTo
    {
        return $this->belongsTo(EventTimeSlot::class);
    }


    public function ageGroups(): HasMany
    {
        return $this->hasMany(AgeGroup::class);
    }

    public function skillLevels(): HasMany
    {
        return $this->hasMany(SkillLevel::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }
}
