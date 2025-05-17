<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Package;
use App\Models\RoomType;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Check if bookings already exist
        if (Booking::count() > 0) {
            $this->command->info('Bookings already exist. Skipping seeder.');
            return;
        }

        // Get all packages and room types
        $packages = Package::all();
        $roomTypes = RoomType::all();

        if ($packages->isEmpty() || $roomTypes->isEmpty()) {
            $this->command->info('No packages or room types found. Please run PackageSeeder and RoomTypeSeeder first.');
            return;
        }

        // Sample booking names
        $names = [
            'John Smith',
            'Maria Garcia',
            'Ahmed Hassan',
            'Li Wei',
            'Sarah Johnson',
            'Mohammed Ali',
            'Emma Wilson',
            'Raj Patel',
            'Sophie Chen',
            'David Kim'
        ];

        // Sample phone numbers
        $phoneNumbers = [
            '60123456789',
            '60198765432',
            '60123456788',
            '60198765431',
            '60123456787',
            '60198765430',
            '60123456786',
            '60198765429',
            '60123456785',
            '60198765428'
        ];

        // Sample IC/Passport numbers
        $icNumbers = [
            'A1234567',
            'B2345678',
            'C3456789',
            'D4567890',
            'E5678901',
            'F6789012',
            'G7890123',
            'H8901234',
            'I9012345',
            'J0123456'
        ];

        // Sample special remarks
        $remarks = [
            'Early check-in requested',
            'Late check-out needed',
            'Allergic to seafood',
            'Extra bed required',
            'Celebrating anniversary',
            'Vegetarian meals preferred',
            'Need airport transfer',
            'Special dietary requirements',
            'Celebrating birthday',
            'Need baby cot'
        ];

        // Create 20 sample bookings
        for ($i = 0; $i < 20; $i++) {
            $package = $packages->random();
            $roomType = $roomTypes->random();
            
            // Generate random dates within the next 6 months
            $startDate = Carbon::now()->addDays(rand(1, 180));
            $endDate = $startDate->copy()->addDays(rand(1, 7));
            
            // Calculate total price (simplified calculation)
            $nights = $startDate->diffInDays($endDate);
            $adults = rand(1, 4);
            $children = rand(0, 3);
            $basePrice = ($roomType->price_per_night * $nights);
            $totalPrice = $basePrice * ($adults + ($children * 0.7)); // Children at 70% of adult price

            Booking::create([
                'package_id' => $package->id,
                'room_type_id' => $roomType->id,
                'booking_name' => $names[$i % count($names)],
                'phone_number' => $phoneNumbers[$i % count($phoneNumbers)],
                'booking_ic' => $icNumbers[$i % count($icNumbers)],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'adults' => $adults,
                'children' => $children,
                'total_price' => round($totalPrice, 2),
                'special_remarks' => $remarks[$i % count($remarks)],
                'status' => ['pending', 'confirmed', 'completed', 'cancelled'][rand(0, 3)],
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 29))
            ]);
        }

        $this->command->info('Bookings seeded successfully!');
    }
} 