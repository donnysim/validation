<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Data\Arr;
use function explode;
use function mb_substr;
use function str_contains;

final class ErrorSegments
{
    protected array $paths = [];

    public function fail(string $path): void
    {
        Arr::set($this->paths, $path ? "{$path}._f" : '_f', true);
    }

    public function hasFailed(string $path): bool
    {
        if ($path === '*') {
            return $this->containsFailedSegments('');
        }

        if (str_contains($path, '*')) {
            return $this->containsFailedSegments(mb_substr($path, 0, mb_strrpos($path, '*') - 1));
        }

        return $this->containsFailedSegments($path);
    }

    protected function containsFailedSegments(string $path): bool
    {
        $parts = explode('.', $path);
        $root = $this->paths;

        foreach ($parts as $part) {
            if (isset($root[$part])) {
                if ($root[$part]['_f'] ?? false) {
                    return true;
                }

                $root = $root[$part];
            } else {
                return false;
            }
        }

        return $root['_f'] ?? false;
    }
}
