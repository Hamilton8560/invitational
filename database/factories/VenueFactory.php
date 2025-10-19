<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Venue;

class VenueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Venue::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'address' => fake()->text(),
            'sports_space_sqft' => fake()->numberBetween(-10000, 10000),
            'spectator_space_sqft' => fake()->numberBetween(-10000, 10000),
            'total_banner_spots' => fake()->numberBetween(-10000, 10000),
            'total_booth_spots' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
