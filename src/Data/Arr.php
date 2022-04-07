<?php

declare(strict_types=1);

namespace DonnySim\Validation\Data;

use function count;
use function explode;
use function is_array;

final class Arr
{
    public static function set(array &$array, string|array $key, mixed $value): void
    {
        $keys = is_array($key) ? $key : explode('.', $key);
        $max = count($keys) - 1;

        foreach ($keys as $index => $k) {
            if ($index === $max) {
                break;
            }

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$k]) || !is_array($array[$k])) {
                $array[$k] = [];
            }

            $array = &$array[$k];
        }

        $array[$keys[$max]] = $value;
    }
}
