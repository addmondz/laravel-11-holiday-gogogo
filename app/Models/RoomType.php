<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'bed_desc',
        'package_id',
        'max_occupancy',
        'max_adults',
        'max_children',
        'max_infants',
        'images',
        'disabled_pax_combinations',
        'default_show_surcharge',
        'sequence',
    ];

    protected $casts = [
        'images' => 'array',
        'disabled_pax_combinations' => 'array',
        'default_show_surcharge' => 'boolean',
    ];

    public function configurations(): HasMany
    {
        return $this->hasMany(PackageConfiguration::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function dateBlockers(): HasMany
    {
        return $this->hasMany(DateBlocker::class);
    }

    /**
     * Scope to filter room types that have available combinations
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithAvailableCombinations(Builder $query): Builder
    {
        // This scope will be applied after loading since we need to check each room type
        // The actual filtering happens in the getAvailableRoomTypes method or via collection filter
        return $query;
    }

    /**
     * Check if this room type has any available (non-disabled) pax combinations
     * 
     * @return bool
     */
    public function hasAvailableCombinations(): bool
    {
        // Generate all possible combinations for this room type
        $allCombinations = $this->generatePaxCombinations();
        
        if (empty($allCombinations)) {
            return false;
        }
        
        // Get disabled combinations
        $disabledCombinations = $this->disabled_pax_combinations ?? [];
        if (!is_array($disabledCombinations)) {
            $disabledCombinations = [];
        }
        
        // Check if all combinations are disabled
        $disabledCount = count($disabledCombinations);
        $totalCount = count($allCombinations);
        
        return $disabledCount < $totalCount;
    }

    /**
     * Generate all possible pax combinations for this room type
     * Uses the same logic as CreatePriceConfigurationsService
     * 
     * @return array
     */
    private function generatePaxCombinations(): array
    {
        $pax = $this->max_occupancy ?? 1;
        if ($pax < 1) {
            return [];
        }

        $maxAdults = $this->max_adults;
        $maxChildren = $this->max_children;
        $maxInfants = $this->max_infants;

        $combinations = [];

        // Total party size from 1 up to $pax
        for ($total = 1; $total <= $pax; $total++) {
            for ($a = 1; $a <= $total; $a++) {
                // Skip if exceeds room type max adults limit
                if ($maxAdults !== null && $a > $maxAdults) {
                    continue;
                }

                for ($c = 0; $c <= $total - $a; $c++) {
                    // Skip if exceeds room type max children limit
                    if ($maxChildren !== null && $c > $maxChildren) {
                        continue;
                    }

                    $i = $total - $a - $c;

                    // Skip if exceeds room type max infants limit
                    if ($maxInfants !== null && $i > $maxInfants) {
                        continue;
                    }

                    $combinations[] = sprintf('%d_a_%d_c_%d_i', $a, $c, $i);
                }
            }
        }

        // Ensure consistent ordering: Adults → Children → Infants
        usort($combinations, function ($x, $y) {
            [$a1, $c1, $i1] = sscanf($x, "%d_a_%d_c_%d_i");
            [$a2, $c2, $i2] = sscanf($y, "%d_a_%d_c_%d_i");

            return [$a1, $c1, $i1] <=> [$a2, $c2, $i2];
        });

        return $combinations;
    }
}
