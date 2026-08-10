<?php

namespace Database\Factories;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * firm_id and user_id are deliberately not defaulted — a profile only makes
     * sense against a specific user, and the composite foreign key means the
     * two must agree. Pass both, or create it through the user's relation.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'ic_number' => fake()->unique()->numerify('######-##-####'),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-21 years'),
            'phone' => fake()->numerify('01#-#######'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
