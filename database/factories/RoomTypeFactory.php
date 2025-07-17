<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => \App\Models\Package::factory(),
            'name' => $this->faker->randomElement(['Deluxe Room', 'Suite', 'Standard Room', 'Family Room']),
            'description' => $this->faker->paragraph(),
            'max_occupancy' => $this->faker->numberBetween(2, 6),
            'images' => [],
        ];
    }
} 