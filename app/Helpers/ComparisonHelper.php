<?php

namespace App\Helpers;

class ComparisonHelper
{
    /**
     * Compare two values, treating null and empty strings as equal.
     *
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    public static function isDifferent($value1, $value2): bool
    {
        // Treat null and empty string as the same
        if (is_null($value1) && $value2 === '') {
            return false;
        }
        if ($value1 === '' && is_null($value2)) {
            return false;
        }

        // Standard comparison for other cases
        return $value1 != $value2;
    }
}
