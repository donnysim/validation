<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Data\Entry;
use Generator;
use function array_key_exists;
use function array_merge;
use function array_slice;
use function explode;
use function implode;
use function is_array;

class PathWalker
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $pattern
     *
     * @return \Generator|\DonnySim\Validation\Data\Entry[]
     */
    public function walk(string $pattern): Generator
    {
        foreach ($this->walkPath($this->data, $pattern, explode('.', $pattern)) as $entry) {
            yield $entry;
        }
    }

    /**
     * @param mixed $data
     * @param string $pattern
     * @param array $segments
     * @param int $position
     * @param string[] $wildcards
     *
     * @return \Generator
     */
    protected function walkPath($data, string $pattern, array $segments, int $position = 0, array $wildcards = []): Generator
    {
        // Make sure the position is not out of bounds.
        if (!isset($segments[$position])) {
            yield new Entry($pattern, $wildcards, $this->getPath($segments, $position - 1), null, false);
            return;
        }

        $key = $segments[$position];

        if ($key === '*') {
            // We cannot loop if it's not an array.
            if (!is_array($data)) {
                yield new Entry($pattern, $wildcards, $this->getPath($segments, $position), null, false);
                return;
            }

            foreach ($data as $dataKey => $dataValue) {
                $dataSegments = $segments;
                $dataSegments[$position] = (string)$dataKey;
                $dataWildcards = array_merge($wildcards, [(string)$dataKey]);

                // Check if last segment in chain.
                if (!isset($segments[$position + 1])) {
                    // Don't increment position because we replaced the wildcard with index.
                    yield new Entry($pattern, $dataWildcards, $this->getPath($dataSegments, $position), $dataValue, true);
                    continue;
                }

                foreach ($this->walkPath($dataValue, $pattern, $dataSegments, $position + 1, $dataWildcards) as $entry) {
                    yield $entry;
                }
            }

            return;
        }

        // Check value because it might not be what we expect from wildcard paths.
        if (!is_array($data) || !array_key_exists($key, $data)) {
            yield new Entry($pattern, $wildcards, $this->getPath($segments, $position), null, false);
            return;
        }

        // Check if last segment in chain.
        if (!isset($segments[$position + 1])) {
            yield new Entry($pattern, $wildcards, $this->getPath($segments, $position), $data[$key], true);
            return;
        }

        // Check if value is an array and we can continue down the tree.
        if (!is_array($data[$key])) {
            yield new Entry($pattern, $wildcards, $this->getPath($segments, $position), null, false);
            return;
        }

        foreach ($this->walkPath($data[$key], $pattern, $segments, $position + 1, $wildcards) as $entry) {
            yield $entry;
        }
    }

    protected function getPath(array $segments, int $position): string
    {
        return implode(
            '.',
            $position ? array_slice($segments, 0, $position + 1) : $segments
        );
    }
}
