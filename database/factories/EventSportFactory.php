<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSport;
use App\Models\EventTimeSlot;
use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventSportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventSport::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'sport_id' => Sport::factory()->create()->onDelete,
            'time_slot_id' => EventTimeSlot::factory()->create()->onDelete,
            'space_required_sqft' => fake()->numberBetween(-10000, 10000),
            'max_teams' => fake()->numberBetween(-10000, 10000),
            'max_players' => fake()->numberBetween(-10000, 10000),
            'event_time_slot_id' => EventTimeSlot::factory(),
        ];
    }
}
