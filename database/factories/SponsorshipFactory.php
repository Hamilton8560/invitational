<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\SponsorPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sponsorship>
 */
class SponsorshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->company();

        return [
            'event_id' => Event::factory(),
            'sponsor_package_id' => SponsorPackage::factory(),
            'buyer_id' => User::factory(),
            'company_name' => $companyName,
            'company_logo_url' => fake()->optional()->imageUrl(),
            'website_url' => fake()->optional()->url(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->optional()->phoneNumber(),
            'status' => 'pending',
            'admin_notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the sponsorship is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the sponsorship is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the sponsorship has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_at' => now()->subMonths(6),
            'expires_at' => now()->subDay(),
        ]);
    }
}
