<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;

class PathWalker
{
    protected ?Closure $onHitCallback = null;

    protected ?Closure $onMissCallback = null;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function onHit(Closure $callback): self
    {
        $this->onHitCallback = $callback;

        return $this;
    }

    public function onMiss(Closure $callback): self
    {
        $this->onMissCallback = $callback;

        return $this;
    }

    public function walk(string $path): void
    {
        $this->walkPath($this->data, \explode('.', $path));
    }

    /**
     * @param mixed $data
     * @param array $segments
     * @param int $position
     * @param string[] $wildcards
     */
    protected function walkPath($data, array $segments, int $position = 0, array $wildcards = []): void
    {
        // Make sure the position is not out of bounds.
        if (!isset($segments[$position])) {
            $this->miss($this->getPath($segments, $position - 1), $wildcards);
            return;
        }

        $key = $segments[$position];

        if ($key === '*') {
            // We cannot loop if it's not an array.
            if (!\is_array($data)) {
                $this->miss($this->getPath($segments, $position), $wildcards);
                return;
            }

            foreach ($data as $dataKey => $dataValue) {
                $dataSegments = $segments;
                $dataSegments[$position] = (string)$dataKey;
                $dataWildcards = \array_merge($wildcards, [(string)$dataKey]);

                // Check if last segment in chain.
                if (!isset($segments[$position + 1])) {
                    // Don't increment position because we replaced the wildcard with index.
                    $this->hit($this->getPath($dataSegments, $position), $dataValue, $dataWildcards);
                    continue;
                }

                $this->walkPath($dataValue, $dataSegments, $position + 1, $dataWildcards);
            }

            return;
        }

        // Check value because it might not be what we expect from wildcard paths.
        if (!\is_array($data) || !\array_key_exists($key, $data)) {
            $this->miss($this->getPath($segments, $position), $wildcards);
            return;
        }

        // Check if last segment in chain.
        if (!isset($segments[$position + 1])) {
            $this->hit($this->getPath($segments, $position), $data[$key], $wildcards);
            return;
        }

        // Check if value is an array and we can continue down the tree.
        if (!\is_array($data[$key])) {
            $this->miss($this->getPath($segments, $position), $wildcards);
            return;
        }

        $this->walkPath($data[$key], $segments, $position + 1, $wildcards);
    }

    protected function getPath(array $segments, int $position): string
    {
        return \implode(
            '.',
            $position ? \array_slice($segments, 0, $position + 1) : $segments
        );
    }

    protected function hit(string $path, $value, array $wildcards): bool
    {
        if ($this->onHitCallback) {
            ($this->onHitCallback)($path, $value, $wildcards);
        }

        return true;
    }

    protected function miss(string $path, array $wildcards): bool
    {
        if ($this->onMissCallback) {
            ($this->onMissCallback)($path, $wildcards);
        }

        return false;
    }
}
