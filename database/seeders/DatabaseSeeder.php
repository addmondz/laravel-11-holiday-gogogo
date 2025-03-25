<?php

use Illuminate\Database\Seeder;
use App\Models\TravelPackage;
use App\Models\AddOn;
use App\Models\SeasonConfiguration;
use App\Models\SeasonDate;
use App\Models\DateType;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // You can set the password as per your choice
        ]);

        // Seed Travel Package
        $package = TravelPackage::create([
            'name' => 'Island Getaway',
            'description' => 'Enjoy 3 days on a beautiful tropical island.',
            'icon_photo' => 'images/packages/island.png',
            'display_price_adult' => 499.99,
            'display_price_child' => 299.99,
            'package_days' => 3,
            'package_min_days' => 3,
            'package_max_days' => 5,
            'tnc' => 'Non-refundable within 7 days of departure.',
            'package_start_date' => now(),
            'package_end_date' => now()->addMonths(6),
            'is_active' => true,
        ]);

        // Seed Add-ons
        AddOn::create([
            'name' => 'Ferry Ticket',
            'description' => 'Round trip ferry ticket.',
            'adult_price' => 50.00,
            'child_price' => 30.00,
            'package_id' => $package->id,
        ]);

        AddOn::create([
            'name' => 'Travel Insurance',
            'description' => 'Full coverage travel insurance.',
            'adult_price' => 20.00,
            'child_price' => 10.00,
            'package_id' => $package->id,
        ]);

        // Seed Season Configurations
        $peakSeason = SeasonConfiguration::create([
            'name' => 'Peak Season',
            'priority' => 1,
        ]);

        SeasonDate::create([
            'season_configuration_id' => $peakSeason->id,
            'start_date' => '2025-12-01',
            'end_date' => '2026-01-15',
        ]);

        // Seed Date Types
        $weekday = DateType::create(['name' => 'Weekday']);
        $weekend = DateType::create(['name' => 'Weekend']);

        // Seed Room Types
        RoomType::create([
            'name' => 'Deluxe Room',
            'season_configuration_id' => $peakSeason->id,
            'date_type_id' => $weekday->id,
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'base_charge_per_adult' => 100.00,
            'base_charge_per_child' => 60.00,
            'surcharge_charge_per_adult' => 20.00,
            'surcharge_charge_per_child' => 10.00,
            'ext_charge_per_adult' => 30.00,
            'ext_charge_per_child' => 15.00,
        ]);
    }
}
