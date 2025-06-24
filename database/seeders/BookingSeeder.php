<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Package;
use App\Models\RoomType;
use App\Services\GenerateBookingUid;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

        // Create sample bookings
        for ($i = 0; $i < 25; $i++) {
            $package = $packages->random();
            // Get room types that belong to this package
            $packageRoomTypes = RoomType::where('package_id', $package->id)->get();
            
            if ($packageRoomTypes->isEmpty()) {
                $this->command->warn("No room types found for package {$package->name}. Skipping...");
                continue;
            }
            
            // Generate random dates within the next 6 months
            $startDate = Carbon::now()->addDays(rand(1, 30)); // Reduced to 30 days for testing
            $endDate = $startDate->copy()->addDays($package->package_max_days);
            
            // Calculate total price (simplified calculation)
            $nights = $startDate->diffInDays($endDate);
            
            // Randomly decide number of rooms (1-3)
            $numberOfRooms = rand(1, 3);
            $totalAdults = 0;
            $totalChildren = 0;
            $totalInfants = 0;
            $totalPrice = 0;
            
            try {
                // Create the booking
                $booking = Booking::create([
                    'package_id' => $package->id,
                    'booking_name' => $names[$i % count($names)],
                    'phone_number' => $phoneNumbers[$i % count($phoneNumbers)],
                    'booking_ic' => $icNumbers[$i % count($icNumbers)],
                    'booking_email' => fake()->email(),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'adults' => 0, // Will be updated after rooms are created
                    'children' => 0, // Will be updated after rooms are created
                    'infants' => 0, // Will be updated after rooms are created
                    'total_price' => 0, // Will be updated after rooms are created
                    'special_remarks' => $remarks[$i % count($remarks)],
                    'uuid' => (new GenerateBookingUid())->execute(),
                    'payment_status' => rand(0, 1) ? 'pending' : 'paid'
                ]);

                // Create rooms for this booking
                for ($j = 0; $j < $numberOfRooms; $j++) {
                    $roomType = $packageRoomTypes->random();
                    $max = min(4, $packageRoomTypes->random()->max_occupancy);
                    $adults = rand(1, $max);
                    $children = rand(0, min(2, $max - $adults));
                    $infants = rand(0, min(2, $max - $adults - $children));
                    
                    // Calculate room price
                    $basePrice = $package->display_price_adult ?? 100.00;
                    $roomPrice = ($basePrice * $nights * $adults) + ($basePrice * 0.7 * $nights * $children) + ($basePrice * 0.3 * $nights * $infants);
                    
                    // Create booking room
                    BookingRoom::create([
                        'booking_id' => $booking->id,
                        'room_type_id' => $roomType->id,
                        'adults' => $adults,
                        'children' => $children,
                        'infants' => $infants
                    ]);
                    
                    // Update totals
                    $totalAdults += $adults;
                    $totalChildren += $children;
                    $totalInfants += $infants;
                    $totalPrice += $roomPrice;
                }
                
                // Update booking with totals
                $booking->update([
                    'adults' => $totalAdults,
                    'children' => $totalChildren,
                    'infants' => $totalInfants,
                    'total_price' => round($totalPrice, 2)
                ]);

            } catch (\Exception $e) {
                $this->command->error("Failed to create booking: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info('Bookings seeded successfully!');
    }
} 