<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\User;

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
