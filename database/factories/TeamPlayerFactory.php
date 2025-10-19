<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\TeamPlayer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamPlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeamPlayer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory()->create()->onDelete,
            'user_id' => User::factory()->create()->onDelete,
            'jersey_number' => fake()->regexify('[A-Za-z0-9]{10}'),
            'position' => fake()->regexify('[A-Za-z0-9]{50}'),
            'emergency_contact_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'emergency_contact_phone' => fake()->regexify('[A-Za-z0-9]{20}'),
            'waiver_signed' => fake()->boolean(),
            'waiver_signed_at' => fake()->dateTime(),
        ];
    }
}
