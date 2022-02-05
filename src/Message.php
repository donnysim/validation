<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Data\DataEntry;
use JsonSerializable;

class Message implements JsonSerializable
{
    protected string $path;

    protected string $failingRuleName;

    protected array $params;

    protected string $attribute;

    public function __construct(string $path, string $attribute, string $failingRuleName, array $params = [])
    {
        $this->path = $path;
        $this->failingRuleName = $failingRuleName;
        $this->params = $params;
        $this->attribute = $attribute;
    }

    public static function make(string $path, string $attribute, string $failingRuleName, array $params = []): self
    {
        return new self($path, $attribute, $failingRuleName, $params);
    }

    public static function forEntry(DataEntry $entry, string $failingRuleName, array $params = []): self
    {
        return new self($entry->getPath(), $entry->getKey(), $failingRuleName, $params);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFailingRuleName(): string
    {
        return $this->failingRuleName;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function jsonSerialize(): array
    {
        return [
            'key' => $this->getFailingRuleName(),
            'params' => $this->getParams(),
        ];
    }
}
