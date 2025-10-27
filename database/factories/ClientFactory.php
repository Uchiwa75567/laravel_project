<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            // Ensure emails are globally unique across seed runs by appending a UUID
            'email' => strtolower($this->faker->unique()->userName() . '+' . Str::uuid() . '@' . $this->faker->safeEmailDomain()),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'postal_code' => $this->faker->postcode(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'last_order_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the client is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the client is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the client has recent orders.
     */
    public function withRecentOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_order_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}
