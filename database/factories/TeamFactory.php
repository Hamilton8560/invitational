<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'division_id' => Division::factory()->create()->onDelete,
            'owner_id' => User::factory()->create()->onDelete,
            'name' => fake()->name(),
            'max_players' => fake()->numberBetween(-10000, 10000),
            'current_players' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
