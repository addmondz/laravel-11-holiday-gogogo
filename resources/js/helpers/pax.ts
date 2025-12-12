/**
 * PAX Combination Helper Functions
 * 
 * These functions generate and parse passenger (pax) combinations
 * in the format: "{adults}_a_{children}_c_{infants}_i"
 * 
 * Example: "2_a_1_c_0_i" means 2 adults, 1 child, 0 infants
 */

/**
 * Generate all possible PAX combinations up to a given total pax
 * 
 * @param pax - Maximum total passengers (adults + children + infants)
 * @param maxAdults - Optional maximum adults limit (null/undefined means no limit)
 * @param maxChild - Optional maximum children limit (null/undefined means no limit)
 * @param maxInfant - Optional maximum infants limit (null/undefined means no limit)
 * @param disabled_pax_combinations - Optional array of disabled combination strings to exclude
 * @returns Array of combination strings in format "X_a_Y_c_Z_i"
 * 
 * @example
 * generatePaxCombinations(3)
 * // Returns: ["1_a_0_c_0_i", "2_a_0_c_0_i", "1_a_1_c_0_i", "3_a_0_c_0_i", "2_a_1_c_0_i", "1_a_2_c_0_i", ...]
 * 
 * generatePaxCombinations(4, 2, 2, 1)
 * // Returns combinations respecting the max limits
 * 
 * generatePaxCombinations(4, 2, 2, 1, ["2_a_1_c_0_i", "1_a_2_c_0_i"])
 * // Returns combinations excluding the disabled ones
 */
export function generatePaxCombinations(
    pax: number,
    maxAdults?: number | null,
    maxChild?: number | null,
    maxInfant?: number | null,
    disabled_pax_combinations?: string[] | null
): string[] {
    if (pax < 1) return [];

    const combinations: string[] = [];

    console.log('maxAdults: ', maxAdults);
    console.log('maxChild: ', maxChild);
    console.log('maxInfant: ', maxInfant);

    // Total party size from 1 up to pax
    for (let total = 1; total <= pax; total++) {
        for (let a = 1; a <= total; a++) {
            // Skip if exceeds max adults limit
            if (maxAdults !== null && maxAdults !== undefined && a > maxAdults) {
                continue;
            }

            for (let c = 0; c <= total - a; c++) {
                // Skip if exceeds max children limit
                if (maxChild !== null && maxChild !== undefined && c > maxChild) {
                    continue;
                }

                const i = total - a - c; // Infants fill the rest

                // Skip if exceeds max infants limit
                if (maxInfant !== null && maxInfant !== undefined && i > maxInfant) {
                    continue;
                }

                combinations.push(`${a}_a_${c}_c_${i}_i`);
            }
        }
    }

    // Ensure consistent ordering: Adults → Children → Infants
    combinations.sort((x, y) => {
        const match1 = x.match(/^(\d+)_a_(\d+)_c_(\d+)_i$/);
        const match2 = y.match(/^(\d+)_a_(\d+)_c_(\d+)_i$/);
        
        if (!match1 || !match2) return 0;

        const a1 = parseInt(match1[1], 10);
        const c1 = parseInt(match1[2], 10);
        const i1 = parseInt(match1[3], 10);
        
        const a2 = parseInt(match2[1], 10);
        const c2 = parseInt(match2[2], 10);
        const i2 = parseInt(match2[3], 10);

        // Compare by adults first, then children, then infants
        if (a1 !== a2) return a1 - a2;
        if (c1 !== c2) return c1 - c2;
        return i1 - i2;
    });

    // Filter out disabled combinations
    if (disabled_pax_combinations && disabled_pax_combinations.length > 0) {
        const disabledSet = new Set(disabled_pax_combinations);
        return combinations.filter(combo => !disabledSet.has(combo));
    }

    return combinations;
}

/**
 * Convert a PAX combination string to human-readable format
 * 
 * @param combination - Combination string in format "X_a_Y_c_Z_i"
 * @returns Human-readable string in format "X Adults + Y Children + Z Infants"
 * 
 * @example
 * formatPaxCombination("2_a_1_c_0_i")
 * // Returns: "2 Adults + 1 Child + 0 Infants"
 * 
 * formatPaxCombination("1_a_0_c_1_i")
 * // Returns: "1 Adult + 0 Children + 1 Infant"
 */
export function formatPaxCombination(combination: string): string {
    const match = combination.match(/^(\d+)_a_(\d+)_c_(\d+)_i$/);
    if (!match) {
        return combination;
    }

    const adults = parseInt(match[1], 10);
    const children = parseInt(match[2], 10);
    const infants = parseInt(match[3], 10);

    const parts: string[] = [];
    
    parts.push(`${adults} ${adults === 1 ? 'Adult' : 'Adults'}`);
    parts.push(`${children} ${children === 1 ? 'Child' : 'Children'}`);
    parts.push(`${infants} ${infants === 1 ? 'Infant' : 'Infants'}`);

    return parts.join(' + ');
}
