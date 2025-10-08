<?php

namespace App\Services;

use App\Models\PackageConfiguration;
use App\Models\RoomType;
use App\Models\SeasonType;
use App\Models\DateType;
use App\Models\DateTypeRange;
use App\Models\Package;
use App\Models\Season;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnsurePriceConfigService
{
    public function __construct(
        private CreatePriceConfigurationsService $priceConfigurationService
    ) {}

    /**
     * Create any missing price configurations for the given roomTypeIds
     * AND ensure only one configuration per room type (dedupe extras).
     *
     * Strict targeting: acts ONLY on the provided $roomTypeIds.
     * Empty $roomTypeIds => no-op (safe guard).
     *
     * @return array{
     *   status:string,
     *   created:int,
     *   deleted:int,
     *   missing:array<int,int>,
     *   kept_per_room_type: array<int,int>
     * }
     */
    public function syncPriceConfigurations(
        int $packageId,
        int $seasonTypeId,
        int $dateTypeId,
        array|Collection $roomTypeIds
    ): array {
        if ($packageId <= 0 || $seasonTypeId <= 0 || $dateTypeId <= 0) {
            throw new \InvalidArgumentException('packageId, seasonTypeId and dateTypeId are required and must be > 0.');
        }

        if (count($roomTypeIds) == 0) {
            $roomTypeIds = RoomType::where('package_id', $packageId)->pluck('id');
        }

        // Normalize & sanitize targets
        $targetIds = ($roomTypeIds instanceof Collection ? $roomTypeIds : collect($roomTypeIds))
            ->filter(fn($id) => $id !== null)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($targetIds->isEmpty()) {
            Log::info('syncPriceConfigurations: No target roomTypeIds provided; skipping.');
            return ['status' => 'no-targets', 'created' => 0, 'deleted' => 0, 'missing' => [], 'kept_per_room_type' => []];
        }

        $result = [
            'status' => 'ok',
            'created' => 0,
            'deleted' => 0,
            'missing' => [],
            'kept_per_room_type' => [], // room_type_id => kept config id
        ];

        DB::transaction(function () use (
            $packageId,
            $seasonTypeId,
            $dateTypeId,
            $targetIds,
            &$result
        ) {
            // ===== Step 1: CREATE MISSING =====
            $existing = PackageConfiguration::where('package_id', $packageId)
                ->where('season_type_id', $seasonTypeId)
                ->where('date_type_id', $dateTypeId)
                ->whereIn('room_type_id', $targetIds->all())
                ->pluck('room_type_id')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            $missing = $targetIds->diff($existing)->values();

            if ($missing->isNotEmpty()) {

                // Log::info('syncPriceConfigurations: missing', [
                //     'missing' => $missing->all(),
                // ]);
                
                $roomTypes  = RoomType::whereIn('id', $missing)->get();
                $seasonType = SeasonType::findOrFail($seasonTypeId);
                $dateType   = DateType::findOrFail($dateTypeId);
                $package    = Package::findOrFail($packageId);

                // Create via your existing creator service
                $this->priceConfigurationService->createPriceConfigurationsService(
                    $package,
                    $roomTypes,
                    [$seasonType],
                    [$dateType],
                    false
                );

                $result['created'] = $roomTypes->count();
                $result['missing'] = $missing->all();
            }

            $this->cleanDuplicatedConfigurations($packageId, $seasonTypeId, $dateTypeId, $targetIds);
        });

        return $result;
    }

    public function syncPriceConfigurationsBySeasonsAndDateTypes(int $packageId, array $seasonTypeIds = [], array $dateTypeIds = [])
    {
        if (empty($seasonTypeIds)) {
            $seasonTypeIds = Season::where('package_id', $packageId)
                ->pluck('season_type_id')
                ->unique()
                ->toArray();
        }

        if (empty($dateTypeIds)) {
            $dateTypeIds = DateTypeRange::where('package_id', $packageId)
                ->pluck('date_type_id')
                ->unique()
                ->toArray();

            $weekdayWeekendIds = DateType::whereIn('name', ['weekend', 'weekday'])
                ->pluck('id')
                ->toArray();

            $dateTypeIds = array_unique(array_merge($dateTypeIds, $weekdayWeekendIds));
        }

        if (count($seasonTypeIds) === 0 || count($dateTypeIds) === 0) {
            Log::info('syncPriceConfigurationsBySeasonsAndDateTypes: No seasonTypeIds or dateTypeIds provided; skipping.');
            return;
        }

        foreach ($seasonTypeIds as $seasonTypeId) {
            foreach ($dateTypeIds as $dateTypeId) {
                $this->syncPriceConfigurations($packageId, $seasonTypeId, $dateTypeId, []);
            }
        }
    }

    private function scopeQuery(int $packageId, int $seasonTypeId, int $dateTypeId): Builder
    {
        return PackageConfiguration::query()
            ->where('package_id', $packageId)
            ->where('season_type_id', $seasonTypeId)
            ->where('date_type_id', $dateTypeId);
    }

    public function cleanDuplicatedConfigurations(int $packageId, int $seasonTypeId, int $dateTypeId, array|Collection $roomTypeIds)
    {
        $targetIds = ($roomTypeIds instanceof Collection ? $roomTypeIds : collect($roomTypeIds))
            ->filter(fn($id) => $id !== null)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        // ===== Step 2: CLEAN DUPLICATES =====
        // Re-query the scoped set for JUST the target room types.
        $rows = $this->scopeQuery($packageId, $seasonTypeId, $dateTypeId)
            ->whereIn('room_type_id', $targetIds->all())
            ->orderBy('id', 'desc') // newest first
            ->get()
            ->groupBy('room_type_id');

        $toDelete = [];

        foreach ($rows as $rtId => $configs) {
            $keeper = $configs->shift();            // newest by id
            $result['kept_per_room_type'][(int)$rtId] = (int)$keeper->id;

            if ($configs->isNotEmpty()) {
                $extraIds = $configs->pluck('id')->all();
                $toDelete = array_merge($toDelete, $extraIds);

                Log::info("syncPriceConfigurations: keeping {$keeper->id} for room_type_id {$rtId}, deleting extras", [
                    'delete_ids' => $extraIds,
                ]);
            }
        }

        if (!empty($toDelete)) {
            Log::info('cleanDuplicatedConfigurations: deleting duplicates', [
                'delete_ids' => $toDelete,
            ]);
            // $deleted = PackageConfiguration::whereIn('id', $toDelete)->delete();
            // $result['deleted'] = (int)$deleted;
        }
    }
}
