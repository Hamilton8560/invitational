<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'date_of_birth',
        'role',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->name;
    }

    /**
     * Relationships
     */
    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamPlayer::class);
    }

    public function individualPlayerRegistrations()
    {
        return $this->hasMany(IndividualPlayer::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function booths()
    {
        return $this->hasMany(Booth::class, 'buyer_id');
    }

    public function banners()
    {
        return $this->hasMany(Banner::class, 'buyer_id');
    }

    public function websiteAds()
    {
        return $this->hasMany(WebsiteAd::class, 'buyer_id');
    }

    public function requestedRefunds()
    {
        return $this->hasMany(Refund::class, 'requested_by');
    }

    public function processedRefunds()
    {
        return $this->hasMany(Refund::class, 'processed_by');
    }
}
