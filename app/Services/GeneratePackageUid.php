<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Facades\Log;

class GeneratePackageUid
{
    public function execute(string $packageName): string
    {
        $initials = implode('', array_map(fn($w) => $w[0], explode(' ', $packageName)));
        $prefix = date('y') . strtoupper($initials);

        do {
            $uid = $prefix . rand(10000, 99999);
            $exists = Package::where('uuid', $uid)->exists();
            if ($exists) {
                Log::channel('packageUidRegeneration')->info("Package UID exists: $uid");
            }
        } while ($exists);

        return $uid;
    }
}

?>