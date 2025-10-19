<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PlayerInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'token',
        'user_id',
        'accepted',
        'accepted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'accepted' => 'boolean',
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }
}
