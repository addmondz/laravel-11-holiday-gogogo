<?php

namespace App\Exports\Sheets;

use App\Models\Package;
use App\Models\PackageConfiguration;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PriceConfigurationSheet implements FromArray, WithTitle, ShouldAutoSize
{
    public function __construct(protected Package $package) {}

    public function title(): string
    {
        return 'Price Configuration';
    }

    public function array(): array
    {
        $configs = PackageConfiguration::with(['roomType', 'seasonType', 'dateType'])
            ->where('package_id', $this->package->id)
            ->get();

        if ($configs->isEmpty()) {
            return [['No price configurations found.']];
        }

        // Group by room_type -> season_type -> date_type
        $grouped = $configs->groupBy([
            fn($c) => $c->room_type_id,
            fn($c) => $c->season_type_id,
            fn($c) => $c->date_type_id,
        ]);

        $rows = [];

        foreach ($grouped as $roomTypeId => $seasonGroups) {
            foreach ($seasonGroups as $seasonTypeId => $dateTypeGroups) {
                foreach ($dateTypeGroups as $dateTypeId => $configGroup) {
                    $config = $configGroup->first();
                    $prices = $config->configuration_prices ?? [];

                    $roomName = $config->roomType->name ?? "Room #$roomTypeId";
                    $seasonName = $config->seasonType->name ?? "Season #$seasonTypeId";
                    $dateTypeName = $config->dateType->name ?? "Date Type #$dateTypeId";

                    // Section header
                    $rows[] = ["Room Type: $roomName | Season: $seasonName | Date Type: $dateTypeName"];
                    $rows[] = []; // blank row

                    $base = $prices[0]['base'] ?? $prices['base'] ?? [];
                    $surch = $prices[0]['surch'] ?? $prices['surch'] ?? [];

                    // --- Base Charges ---
                    if (!empty($base)) {
                        $sortedComboKeys = $this->sortComboKeys(array_keys($base));

                        // Determine max slot columns from data
                        $allSlotKeys = [];
                        foreach ($base as $slots) {
                            foreach (array_keys($slots) as $sk) {
                                $allSlotKeys[$sk] = true;
                            }
                        }
                        $orderedSlots = $this->orderSlotKeys(array_keys($allSlotKeys));

                        // Header row
                        $headerRow = ['Base Charges - Pax Combination'];
                        foreach ($orderedSlots as $sk) {
                            $headerRow[] = $this->slotLabel($sk);
                        }
                        $rows[] = $headerRow;

                        // Data rows
                        foreach ($sortedComboKeys as $comboKey) {
                            $slots = $base[$comboKey] ?? [];
                            $dataRow = [$this->comboVerbose($comboKey)];
                            foreach ($orderedSlots as $sk) {
                                $dataRow[] = isset($slots[$sk]) ? number_format((float) $slots[$sk], 2) : '';
                            }
                            $rows[] = $dataRow;
                        }

                        $rows[] = []; // blank row
                    }

                    // --- Surcharges ---
                    if (!empty($surch)) {
                        $sortedComboKeys = $this->sortComboKeys(array_keys($surch));

                        $headerRow = ['Surcharges - Pax Combination', 'Adult per pax', 'Child per pax', 'Infant per pax'];
                        $rows[] = $headerRow;

                        foreach ($sortedComboKeys as $comboKey) {
                            $slots = $surch[$comboKey] ?? [];
                            $rows[] = [
                                $this->comboVerbose($comboKey),
                                isset($slots['a']) ? number_format((float) $slots['a'], 2) : '',
                                isset($slots['c']) ? number_format((float) $slots['c'], 2) : '',
                                isset($slots['i']) ? number_format((float) $slots['i'], 2) : '',
                            ];
                        }

                        $rows[] = []; // blank row
                    }

                    $rows[] = []; // extra spacing between sections
                }
            }
        }

        return $rows;
    }

    private function comboVerbose(string $key): string
    {
        if (!preg_match('/^(\d+)_a_(\d+)_c_(\d+)_i$/', $key, $m)) {
            return $key;
        }

        $a = (int) $m[1];
        $c = (int) $m[2];
        $i = (int) $m[3];

        $plural = fn($n, $s, $p = null) => "$n " . ($n === 1 ? $s : ($p ?? $s . 's'));

        return $plural($a, 'Adult') . ' + ' . $plural($c, 'Child', 'Children') . ' + ' . $plural($i, 'Infant');
    }

    private function sortComboKeys(array $keys): array
    {
        usort($keys, function ($k1, $k2) {
            $p1 = $this->parseCombo($k1);
            $p2 = $this->parseCombo($k2);
            // Descending by adults, then children, then infants
            if ($p1['a'] !== $p2['a']) return $p2['a'] - $p1['a'];
            if ($p1['c'] !== $p2['c']) return $p2['c'] - $p1['c'];
            return $p2['i'] - $p1['i'];
        });

        return $keys;
    }

    private function parseCombo(string $key): array
    {
        if (preg_match('/^(\d+)_a_(\d+)_c_(\d+)_i$/', $key, $m)) {
            return ['a' => (int) $m[1], 'c' => (int) $m[2], 'i' => (int) $m[3]];
        }
        return ['a' => 0, 'c' => 0, 'i' => 0];
    }

    private function slotLabel(string $key): string
    {
        $prefix = $key[0] ?? '';
        $num = substr($key, 1);
        return match ($prefix) {
            'a' => "Adult $num",
            'c' => "Child $num",
            'i' => "Infant $num",
            default => $key,
        };
    }

    private function orderSlotKeys(array $keys): array
    {
        usort($keys, function ($x, $y) {
            $order = fn($k) => (['a' => 1, 'c' => 2, 'i' => 3][$k[0]] ?? 9) * 100 + (int) substr($k, 1);
            return $order($x) - $order($y);
        });

        return $keys;
    }
}
