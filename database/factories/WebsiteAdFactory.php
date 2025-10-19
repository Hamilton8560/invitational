<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\Product;
use App\Models\User;
use App\Models\WebsiteAd;

class WebsiteAdFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WebsiteAd::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'product_id' => Product::factory()->create()->onDelete,
            'buyer_id' => User::factory()->create()->onDelete,
            'ad_placement' => fake()->randomElement(["header","sidebar","footer","popup"]),
            'company_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'ad_image_url' => fake()->text(),
            'ad_link_url' => fake()->text(),
            'contact_email' => fake()->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
