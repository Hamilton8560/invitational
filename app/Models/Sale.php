<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'paddle_transaction_id',
        'paddle_subscription_id',
        'payment_method',
        'team_id',
        'individual_player_id',
        'booth_id',
        'banner_id',
        'website_ad_id',
        'sponsorship_id',
        'purchased_at',
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
            'user_id' => 'integer',
            'product_id' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'team_id' => 'integer',
            'individual_player_id' => 'integer',
            'booth_id' => 'integer',
            'banner_id' => 'integer',
            'website_ad_id' => 'integer',
            'sponsorship_id' => 'integer',
            'purchased_at' => 'timestamp',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function individualPlayer(): BelongsTo
    {
        return $this->belongsTo(IndividualPlayer::class);
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function websiteAd(): BelongsTo
    {
        return $this->belongsTo(WebsiteAd::class);
    }

    public function sponsorship(): BelongsTo
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
