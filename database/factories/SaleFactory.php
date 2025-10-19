<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Banner;
use App\Models\Booth;
use App\Models\Event;
use App\Models\IndividualPlayer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Team;
use App\Models\User;
use App\Models\WebsiteAd;

class SaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'user_id' => User::factory()->create()->onDelete,
            'product_id' => Product::factory()->create()->onDelete,
            'quantity' => fake()->numberBetween(-10000, 10000),
            'unit_price' => fake()->randomFloat(2, 0, 99999999.99),
            'total_amount' => fake()->randomFloat(2, 0, 99999999.99),
            'status' => fake()->randomElement(["pending","completed","failed","refunded"]),
            'paddle_transaction_id' => fake()->regexify('[A-Za-z0-9]{255}'),
            'paddle_subscription_id' => fake()->regexify('[A-Za-z0-9]{255}'),
            'payment_method' => fake()->regexify('[A-Za-z0-9]{50}'),
            'team_id' => Team::factory()->create()->onDelete,
            'individual_player_id' => IndividualPlayer::factory()->create()->onDelete,
            'booth_id' => Booth::factory()->create()->onDelete,
            'banner_id' => Banner::factory()->create()->onDelete,
            'website_ad_id' => WebsiteAd::factory()->create()->onDelete,
            'purchased_at' => fake()->dateTime(),
        ];
    }
}
