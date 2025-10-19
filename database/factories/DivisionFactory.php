<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AgeGroup;
use App\Models\Division;
use App\Models\EventSport;
use App\Models\SkillLevel;

class DivisionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Division::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_sport_id' => EventSport::factory()->create()->onDelete,
            'age_group_id' => AgeGroup::factory()->create()->onDelete,
            'skill_level_id' => SkillLevel::factory()->create()->onDelete,
            'name' => fake()->name(),
            'max_teams' => fake()->numberBetween(-10000, 10000),
            'max_players' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
