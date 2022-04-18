<?php

declare(strict_types=1);

namespace DonnySim\Validation\Data;

use Generator;
use function array_key_exists;
use function array_merge;
use function explode;
use function implode;
use function is_array;

final class DataWalker
{
    public static function walk(array $data, string $pattern): Generator
    {
        foreach (self::walkPath($data, $pattern, explode('.', $pattern)) as $entry) {
            yield $entry;
        }
    }

    private static function walkPath(
        mixed $data,
        string $pattern,
        array $segments,
        int $position = 0,
        array $wildcards = []
    ): Generator {
        // Make sure the position is not out of bounds.
        if (!isset($segments[$position])) {
            yield new DataEntry($pattern, $wildcards, self::getPath($segments, $position - 1), null, false);

            return;
        }

        $key = $segments[$position];

        if ($key === '*') {
            // We cannot loop if it's not an array.
            if (!is_array($data)) {
                yield new DataEntry($pattern, $wildcards, self::getPath($segments, $position), null, false);

                return;
            }

            foreach ($data as $dataKey => $dataValue) {
                $dataSegments = $segments;
                $dataSegments[$position] = $dataKey;
                $dataWildcards = array_merge($wildcards, [$dataKey]);

                // Check if last segment in chain.
                if (!isset($segments[$position + 1])) {
                    // Don't increment position because we replaced the wildcard with index.
                    yield new DataEntry($pattern, $dataWildcards, self::getPath($dataSegments, $position), $dataValue, true);
                    continue;
                }

                foreach (self::walkPath($dataValue, $pattern, $dataSegments, $position + 1, $dataWildcards) as $entry) {
                    yield $entry;
                }
            }

            return;
        }

        // Check value because it might not be what we expect from wildcard paths.
        if (!is_array($data) || !array_key_exists($key, $data)) {
            yield new DataEntry($pattern, $wildcards, self::getPath($segments, $position), null, false);

            return;
        }

        // Check if last segment in chain.
        if (!isset($segments[$position + 1])) {
            yield new DataEntry($pattern, $wildcards, self::getPath($segments, $position), $data[$key], true);

            return;
        }

        // Check if value is an array, and we can continue down the tree.
        if (!is_array($data[$key])) {
            yield new DataEntry($pattern, $wildcards, self::getPath($segments, $position), null, false);

            return;
        }

        foreach (self::walkPath($data[$key], $pattern, $segments, $position + 1, $wildcards) as $entry) {
            yield $entry;
        }
    }

    private static function getPath(array $segments, int $position): string
    {
        if ($position === 0) {
            return implode('.', $segments);
        }

        $result = '';

        // We could use array_slice but that costs unnecessary memory overhead.
        for ($i = 0; $i < $position + 1; $i++) {
            if ($i === 0) {
                $result = $segments[$i];
            } else {
                $result .= ".{$segments[$i]}";
            }
        }

        return $result;
    }
}
