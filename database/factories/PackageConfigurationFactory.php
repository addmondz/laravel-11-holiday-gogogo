<?php

namespace Database\Factories;

use App\Models\PackageConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackageConfiguration>
 */
class PackageConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => 1,
            'room_type_id' => 1,
            'season_type_id' => 1,
            'date_type_id' => 1,
            'configuration_prices' => json_encode([
                'b' => [
                    '1_a_0_c_0_i_a' => 100.00,
                    '1_a_0_c_0_i_c' => 50.00,
                    '1_a_0_c_0_i_i' => 0.00,
                ],
                's' => [
                    '1_a_0_c_0_i_a' => 20.00,
                    '1_a_0_c_0_i_c' => 10.00,
                    '1_a_0_c_0_i_i' => 0.00,
                ]
            ]),
        ];
    }
} 