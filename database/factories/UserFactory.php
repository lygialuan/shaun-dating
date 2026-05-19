<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $userName = $this->faker->userName();
        return [
            'name' => $name,
            'user_name' => $userName,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role_id' => 2,
            'password' => Hash::make('123456'),
            'ref_code' => uniqid(),
            'email_verified' => true,
            'hashtags' => 'girl fun wedding friends',
            'is_active' => true
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
}
