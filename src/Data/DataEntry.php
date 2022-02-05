<?php

declare(strict_types=1);

namespace DonnySim\Validation\Data;

use function mb_substr;
use function preg_replace_callback;
use function str_contains;

final class DataEntry
{
    protected string $pattern;

    protected array $wildcards;

    protected string $path;

    protected mixed $value;

    protected bool $present;

    public function __construct(string $pattern, array $wildcards, string $path, mixed $value, bool $present)
    {
        $this->pattern = $pattern;
        $this->wildcards = $wildcards;
        $this->path = $path;
        $this->value = $value;
        $this->present = $present;
    }

    public function getWildcards(): array
    {
        return $this->wildcards;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getKey(): string
    {
        if (str_contains('.', $this->path)) {
            return mb_substr($this->path, mb_strrpos($this->path, '.') + 1);
        }

        return $this->path;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    public function isNotPresent(): bool
    {
        return !$this->present;
    }

    public function resolveSegmentWildcards(string $path): string
    {
        $index = 0;

        return preg_replace_callback('/\*/', function () use (&$index) {
            return $this->wildcards[$index++] ?? '*';
        }, $path);
    }
}
