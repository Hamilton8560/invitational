<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\EventTimeSlot;

class EventTimeSlotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventTimeSlot::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->onDelete,
            'start_time' => fake()->dateTime(),
            'end_time' => fake()->dateTime(),
            'available_space_sqft' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
