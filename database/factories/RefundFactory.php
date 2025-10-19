<?php

namespace Database\Factories;

use App\Models\Refund;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefundFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Refund::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory()->create()->onDelete,
            'amount' => fake()->randomFloat(2, 0, 99999999.99),
            'reason' => fake()->text(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'paddle_refund_id' => fake()->regexify('[A-Za-z0-9]{255}'),
            'requested_by' => User::factory()->create()->onDelete,
            'requested_at' => fake()->dateTime(),
            'processed_at' => fake()->dateTime(),
            'processed_by' => User::factory()->create()->onDelete,
            'requested_by_id' => User::factory(),
            'processed_by_id' => User::factory(),
        ];
    }
}
