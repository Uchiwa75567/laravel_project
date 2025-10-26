<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $firstName . ' ' . $lastName,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(['user', 'manager', 'admin']),
            'is_active' => fake()->boolean(90), // 90% chance of being active
            'last_login_at' => fake()->optional(0.7)->dateTimeBetween('-6 months', 'now'),
            'preferences' => [
                'language' => fake()->randomElement(['fr', 'en', 'es', 'de']),
                'theme' => fake()->randomElement(['light', 'dark']),
                'notifications' => fake()->boolean(),
            ],
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Create a manager user.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
            'is_active' => true,
        ]);
    }

    /**
     * Create a regular user.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a user with specific preferences.
     */
    public function withPreferences(array $preferences): static
    {
        return $this->state(fn (array $attributes) => [
            'preferences' => array_merge($attributes['preferences'] ?? [], $preferences),
        ]);
    }

    /**
     * Create a user with French preferences.
     */
    public function french(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferences' => array_merge($attributes['preferences'] ?? [], [
                'language' => 'fr',
            ]),
        ]);
    }

    /**
     * Create a user with recent login.
     */
    public function recentlyLoggedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create a user with a specific phone number.
     */
    public function withPhone(string $phone): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => $phone,
        ]);
    }
}
