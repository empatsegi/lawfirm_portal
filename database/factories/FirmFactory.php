<?php

namespace Database\Factories;

use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Firm>
 */
class FirmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' & Partners';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'registration_no' => fake()->numerify('##########').'-'.fake()->randomLetter(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('01#-#######'),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the firm is suspended.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
