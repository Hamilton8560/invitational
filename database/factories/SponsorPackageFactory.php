<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SponsorPackage>
 */
class SponsorPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tiers = ['bronze', 'silver', 'gold'];
        $tier = fake()->randomElement($tiers);

        return [
            'event_id' => Event::factory(),
            'tier' => $tier,
            'name' => ucfirst($tier).' Package',
            'description' => fake()->sentence(),
            'price' => match ($tier) {
                'gold' => fake()->numberBetween(5000, 10000),
                'silver' => fake()->numberBetween(2500, 5000),
                'bronze' => fake()->numberBetween(1000, 2500),
            },
            'max_quantity' => fake()->optional()->numberBetween(1, 10),
            'current_quantity' => 0,
            'is_active' => true,
            'is_template' => false,
            'display_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the package is a template.
     */
    public function template(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_template' => true,
            'event_id' => null,
        ]);
    }

    /**
     * Indicate that the package is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a gold tier package.
     */
    public function gold(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => 'gold',
            'name' => 'Gold Package',
            'price' => fake()->numberBetween(5000, 10000),
        ]);
    }

    /**
     * Create a silver tier package.
     */
    public function silver(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => 'silver',
            'name' => 'Silver Package',
            'price' => fake()->numberBetween(2500, 5000),
        ]);
    }

    /**
     * Create a bronze tier package.
     */
    public function bronze(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => 'bronze',
            'name' => 'Bronze Package',
            'price' => fake()->numberBetween(1000, 2500),
        ]);
    }
}
