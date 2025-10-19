<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventTemplate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'source_event_id' => Event::factory()->create()->onDelete,
            'name' => fake()->name(),
            'description' => fake()->text(),
            'created_by' => User::factory()->create()->onDelete,
            'creator_id' => User::factory(),
        ];
    }
}
