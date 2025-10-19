<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Event;
use App\Models\IndividualPlayer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IndividualPlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = IndividualPlayer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'division_id' => Division::factory()->create()->onDelete,
            'user_id' => User::factory()->create()->onDelete,
            'skill_rating' => fake()->randomFloat(1, 0, 99.9),
            'emergency_contact_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'emergency_contact_phone' => fake()->regexify('[A-Za-z0-9]{20}'),
            'waiver_signed' => fake()->boolean(),
            'waiver_signed_at' => fake()->dateTime(),
        ];
    }
}
