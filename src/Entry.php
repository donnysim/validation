<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use function preg_replace_callback;

class Entry
{
    protected string $path;

    protected bool $exists;

    protected string $pattern;

    /**
     * @var string[]
     */
    protected array $wildcards = [];

    /**
     * @var mixed
     */
    protected $value;

    public function __construct(string $pattern, array $wildcards, string $path, $value, bool $exists)
    {
        $this->pattern = $pattern;
        $this->wildcards = $wildcards;
        $this->path = $path;
        $this->value = $value;
        $this->exists = $exists;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function isMissing(): bool
    {
        return !$this->exists;
    }

    public function resolvePathWildcards(string $path): string
    {
        $index = 0;

        return preg_replace_callback('/\*/', function () use (&$index) {
            return $this->wildcards[$index++] ?? '*';
        }, $path);
    }
}
