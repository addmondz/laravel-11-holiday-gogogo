<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Facades\Log;

class GeneratePackageUid
{
    public function execute(string $packageName): string
    {
        // Normalize: keep only letters/digits, turn everything else into spaces, then split
        $clean = preg_replace('/[^A-Za-z0-9]+/', ' ', $packageName);
        $words = preg_split('/\s+/', strtoupper(trim($clean)), -1, PREG_SPLIT_NO_EMPTY);

        // Build initials from first character of each cleaned word (A–Z, 0–9 only)
        $initials = implode('', array_map(fn($w) => $w[0], $words));
        if ($initials === '') {
            $initials = 'PKG'; // fallback if name has no alphanumerics
        }

        $prefix = date('y') . $initials;

        do {
            // 5 digits, zero-padded; use random_int for better randomness
            $uid = $prefix . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $exists = Package::where('uuid', $uid)->exists();

            if ($exists) {
                Log::channel('packageUidRegeneration')->info("Package UID exists: {$uid}");
            }
        } while ($exists);

        return $uid;
    }
}
