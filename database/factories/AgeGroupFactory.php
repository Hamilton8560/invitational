<?php

namespace Database\Factories;

use App\Models\AgeGroup;
use App\Models\EventSport;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgeGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AgeGroup::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_sport_id' => EventSport::factory()->create()->onDelete,
            'name' => fake()->name(),
            'min_age' => fake()->numberBetween(-10000, 10000),
            'max_age' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
