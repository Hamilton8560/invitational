<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Division;
use App\Models\Event;
use App\Models\Product;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'type' => fake()->randomElement(["team","player","spectator","booth","banner","website_ad"]),
            'name' => fake()->name(),
            'description' => fake()->text(),
            'price' => fake()->randomFloat(2, 0, 99999999.99),
            'max_quantity' => fake()->numberBetween(-10000, 10000),
            'current_quantity' => fake()->numberBetween(-10000, 10000),
            'division_id' => Division::factory()->create()->onDelete,
        ];
    }
}
