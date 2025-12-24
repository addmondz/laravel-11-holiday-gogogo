<?php

namespace App\Services;

use App\Models\AppSetting;

class SstCalculationService
{
    /**
     * Calculate SST amount for a given amount
     */
    public static function calculateSst($amount): float
    {
        $sstConfig = AppSetting::getSstConfiguration();

        if ($sstConfig['status'] != 1) {
            return 0.0;
        }

        $sstPercent = (float)($sstConfig['sst_percent'] ?? 6.0);
        return ($amount * $sstPercent) / 100;
    }

    /**
     * Calculate total amount including SST
     */
    public static function calculateTotalWithSst($amount): float
    {
        $sstAmount = self::calculateSst($amount);
        return $amount + $sstAmount;
    }

    /**
     * Get SST percentage
     */
    public static function getSstPercentage(): float
    {
        $sstConfig = AppSetting::getSstConfiguration();
        return $sstConfig['sst_percent'] ?? 6.0;
    }

    /**
     * Check if SST is enabled
     */
    public static function isSstEnabled(): bool
    {
        $sstConfig = AppSetting::getSstConfiguration();
        return $sstConfig['status'] == 1;
    }

    /**
     * Format SST amount for display
     */
    public static function formatSstAmount($amount): string
    {
        return number_format($amount, 2);
    }
}
