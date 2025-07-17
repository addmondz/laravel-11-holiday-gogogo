<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\SeasonType;
use App\Models\DateType;
use App\Models\Season;
use App\Models\DateTypeRange;
use App\Models\PackageConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class GuestBreakdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_breakdown_is_included_in_price_calculation()
    {
        // Create test data
        $package = Package::factory()->create([
            'package_start_date' => '2025-01-01',
            'package_end_date' => '2025-12-31',
            'package_max_days' => 7,
        ]);

        $roomType = RoomType::factory()->create([
            'name' => 'Deluxe Room',
            'max_occupancy' => 4,
        ]);

        $seasonType = SeasonType::factory()->create(['name' => 'Peak']);
        $dateType = DateType::factory()->create(['name' => 'Weekend']);

        // Create season and date type ranges
        Season::factory()->create([
            'package_id' => $package->id,
            'season_type_id' => $seasonType->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ]);

        DateTypeRange::factory()->create([
            'package_id' => $package->id,
            'date_type_id' => $dateType->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ]);

        // Create package configuration with pricing
        $configuration = PackageConfiguration::factory()->create([
            'package_id' => $package->id,
            'room_type_id' => $roomType->id,
            'season_type_id' => $seasonType->id,
            'date_type_id' => $dateType->id,
            'configuration_prices' => json_encode([
                'b' => [
                    '2_a_1_c_0_i_a' => 100.00, // 2 adults, 1 child, 0 infants - adult price
                    '2_a_1_c_0_i_c' => 50.00,  // 2 adults, 1 child, 0 infants - child price
                    '2_a_1_c_0_i_i' => 0.00,   // 2 adults, 1 child, 0 infants - infant price
                ],
                's' => [
                    '2_a_1_c_0_i_a' => 20.00,  // 2 adults, 1 child, 0 infants - adult surcharge
                    '2_a_1_c_0_i_c' => 10.00,  // 2 adults, 1 child, 0 infants - child surcharge
                    '2_a_1_c_0_i_i' => 0.00,   // 2 adults, 1 child, 0 infants - infant surcharge
                ]
            ])
        ]);

        // Make API request
        $response = $this->postJson('/quotation/api/package-calculate-price', [
            'package_id' => $package->id,
            'rooms' => [
                [
                    'room_type' => $roomType->id,
                    'adults' => 2,
                    'children' => 1,
                    'infants' => 0,
                ]
            ],
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-03', // 2 nights
        ]);

        // Assert response structure
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'currency',
                    'nights',
                    'rooms',
                    'guest_breakdown',
                    'summary' => [
                        'total_nights',
                        'total_adults',
                        'total_children',
                        'total_infants',
                        'base_charges',
                        'surcharges',
                        'grand_total'
                    ],
                    'total'
                ]);

        $data = $response->json();

        // Assert guest breakdown exists and has correct structure
        $this->assertArrayHasKey('guest_breakdown', $data);
        $this->assertCount(3, $data['guest_breakdown']); // 2 adults + 1 child

        // Check first adult breakdown
        $firstAdult = $data['guest_breakdown']['adult_0_1'];
        $this->assertEquals(1, $firstAdult['room_number']);
        $this->assertEquals(1, $firstAdult['guest_number']);
        $this->assertEquals('adult', $firstAdult['guest_type']);
        $this->assertEquals('Deluxe Room', $firstAdult['room_type_name']);
        $this->assertEquals(2, $firstAdult['nights']); // 2 nights
        $this->assertEquals(100.00, $firstAdult['base_charge']['price_per_night']);
        $this->assertEquals(200.00, $firstAdult['base_charge']['total']); // 100 * 2 nights
        $this->assertEquals(20.00, $firstAdult['surcharge']['price_per_night']);
        $this->assertEquals(40.00, $firstAdult['surcharge']['total']); // 20 * 2 nights
        $this->assertEquals(240.00, $firstAdult['total']); // (100 + 20) * 2 nights

        // Check second adult breakdown
        $secondAdult = $data['guest_breakdown']['adult_0_2'];
        $this->assertEquals(1, $secondAdult['room_number']);
        $this->assertEquals(2, $secondAdult['guest_number']);
        $this->assertEquals('adult', $secondAdult['guest_type']);
        $this->assertEquals('Deluxe Room', $secondAdult['room_type_name']);
        $this->assertEquals(2, $secondAdult['nights']);
        $this->assertEquals(100.00, $secondAdult['base_charge']['price_per_night']);
        $this->assertEquals(200.00, $secondAdult['base_charge']['total']);
        $this->assertEquals(20.00, $secondAdult['surcharge']['price_per_night']);
        $this->assertEquals(40.00, $secondAdult['surcharge']['total']);
        $this->assertEquals(240.00, $secondAdult['total']);

        // Check child breakdown
        $child = $data['guest_breakdown']['child_0_1'];
        $this->assertEquals(1, $child['room_number']);
        $this->assertEquals(1, $child['guest_number']);
        $this->assertEquals('child', $child['guest_type']);
        $this->assertEquals('Deluxe Room', $child['room_type_name']);
        $this->assertEquals(2, $child['nights']);
        $this->assertEquals(50.00, $child['base_charge']['price_per_night']);
        $this->assertEquals(100.00, $child['base_charge']['total']); // 50 * 2 nights
        $this->assertEquals(10.00, $child['surcharge']['price_per_night']);
        $this->assertEquals(20.00, $child['surcharge']['total']); // 10 * 2 nights
        $this->assertEquals(120.00, $child['total']); // (50 + 10) * 2 nights

        // Assert totals in summary
        $this->assertEquals(2, $data['summary']['total_adults']);
        $this->assertEquals(1, $data['summary']['total_children']);
        $this->assertEquals(0, $data['summary']['total_infants']);
    }

    public function test_guest_breakdown_with_multiple_rooms()
    {
        // Create test data
        $package = Package::factory()->create([
            'package_start_date' => '2025-01-01',
            'package_end_date' => '2025-12-31',
            'package_max_days' => 7,
        ]);

        $roomType1 = RoomType::factory()->create(['name' => 'Deluxe Room', 'max_occupancy' => 4]);
        $roomType2 = RoomType::factory()->create(['name' => 'Suite', 'max_occupancy' => 4]);

        $seasonType = SeasonType::factory()->create(['name' => 'Peak']);
        $dateType = DateType::factory()->create(['name' => 'Weekend']);

        // Create season and date type ranges
        Season::factory()->create([
            'package_id' => $package->id,
            'season_type_id' => $seasonType->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ]);

        DateTypeRange::factory()->create([
            'package_id' => $package->id,
            'date_type_id' => $dateType->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ]);

        // Create package configurations
        foreach ([$roomType1, $roomType2] as $roomType) {
            PackageConfiguration::factory()->create([
                'package_id' => $package->id,
                'room_type_id' => $roomType->id,
                'season_type_id' => $seasonType->id,
                'date_type_id' => $dateType->id,
                'configuration_prices' => json_encode([
                    'b' => [
                        '1_a_0_c_0_i_a' => 100.00, // 1 adult price
                    ],
                    's' => [
                        '1_a_0_c_0_i_a' => 20.00,  // 1 adult surcharge
                    ]
                ])
            ]);
        }

        // Make API request with 2 rooms, 1 adult each
        $response = $this->postJson('/quotation/api/package-calculate-price', [
            'package_id' => $package->id,
            'rooms' => [
                [
                    'room_type' => $roomType1->id,
                    'adults' => 1,
                    'children' => 0,
                    'infants' => 0,
                ],
                [
                    'room_type' => $roomType2->id,
                    'adults' => 1,
                    'children' => 0,
                    'infants' => 0,
                ]
            ],
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-02', // 1 night
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Should have 2 adults total
        $this->assertEquals(2, $data['summary']['total_adults']);
        $this->assertEquals(0, $data['summary']['total_children']);
        $this->assertEquals(0, $data['summary']['total_infants']);
        $this->assertCount(2, $data['guest_breakdown']);

        // Check first room adult
        $firstAdult = $data['guest_breakdown']['adult_0_1'];
        $this->assertEquals(1, $firstAdult['room_number']);
        $this->assertEquals('adult', $firstAdult['guest_type']);
        $this->assertEquals('Deluxe Room', $firstAdult['room_type_name']);

        // Check second room adult
        $secondAdult = $data['guest_breakdown']['adult_1_1'];
        $this->assertEquals(2, $secondAdult['room_number']);
        $this->assertEquals('adult', $secondAdult['guest_type']);
        $this->assertEquals('Suite', $secondAdult['room_type_name']);
    }
} 