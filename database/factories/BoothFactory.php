<?php

namespace Database\Factories;

use App\Models\Booth;
use App\Models\Event;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoothFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booth::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'product_id' => Product::factory()->create()->onDelete,
            'buyer_id' => User::factory()->create()->onDelete,
            'booth_number' => fake()->numberBetween(-10000, 10000),
            'company_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'contact_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'contact_email' => fake()->regexify('[A-Za-z0-9]{255}'),
            'contact_phone' => fake()->regexify('[A-Za-z0-9]{20}'),
        ];
    }
}
