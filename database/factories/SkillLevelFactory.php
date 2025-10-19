<?php

namespace Database\Factories;

use App\Models\EventSport;
use App\Models\SkillLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillLevelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SkillLevel::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_sport_id' => EventSport::factory()->create()->onDelete,
            'name' => fake()->name(),
            'min_rating' => fake()->randomFloat(1, 0, 99.9),
            'max_rating' => fake()->randomFloat(1, 0, 99.9),
        ];
    }
}
