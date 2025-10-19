<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillLevel extends Model
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
        'min_rating',
        'max_rating',
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
            'min_rating' => 'decimal:1',
            'max_rating' => 'decimal:1',
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
}
