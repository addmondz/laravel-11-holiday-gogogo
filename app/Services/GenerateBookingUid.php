<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class GenerateBookingUid
{
    public function execute(): string
    {
        // generate YYabc-12345 format
        do {
            $uid = date('y') . strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3)) . '-' . rand(10000, 99999);
            $exists = Booking::where('uuid', $uid)->exists();
            if ($exists) Log::channel('bookingUidRegeneration')->info("Booking UID exists: $uid");
        } while ($exists);

        return $uid;
    }
}
