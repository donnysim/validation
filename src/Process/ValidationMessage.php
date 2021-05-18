<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use JsonSerializable;

class ValidationMessage implements JsonSerializable
{
    protected DataEntry $entry;

    protected string $key;

    protected array $params = [];

    public function __construct(DataEntry $entry, string $key, array $params = [])
    {
        $this->entry = $entry;
        $this->key = $key;
        $this->params = $params;
    }

    public function getEntry(): DataEntry
    {
        return $this->entry;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function jsonSerialize(): array
    {
        return [
            'key' => $this->getKey(),
            ...$this->getParams(),
        ];
    }
}
