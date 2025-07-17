<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->city(),
            'package_start_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'package_end_date' => $this->faker->dateTimeBetween('+1 year', '+2 years'),
            'package_min_days' => $this->faker->numberBetween(1, 3),
            'package_max_days' => $this->faker->numberBetween(1, 14),
            'display_price_adult' => $this->faker->randomFloat(2, 100, 1000),
            'display_price_child' => $this->faker->randomFloat(2, 50, 500),
            'display_price_infant' => $this->faker->randomFloat(2, 0, 100),
            'terms_and_conditions' => $this->faker->paragraph(),
            'child_max_age_desc' => '0-12 years',
            'infant_max_age_desc' => '0-2 years',
            'images' => [],
        ];
    }
} 