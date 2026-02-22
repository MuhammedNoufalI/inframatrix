<?php

namespace App\Imports;

trait HandlesFuzzyMatching
{
    /**
     * Attempts to perform a stabilized fuzzy match.
     * Trims, lowercases, and removes all spaces, hyphens, and underscores.
     * Example: "cloud-panel" perfectly matches "CloudPanel". 
     */
    public function fuzzyMatch(string $input, array $allowedValues): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        $normalizedInput = strtolower(preg_replace('/[\s\-_]+/', '', $input));

        foreach ($allowedValues as $val) {
            $normalizedVal = strtolower(preg_replace('/[\s\-_]+/', '', $val));
            if ($normalizedInput === $normalizedVal) {
                return $val;
            }
        }

        return null;
    }
}
