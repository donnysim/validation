<?php

declare(strict_types=1);

namespace DonnySim\Validation;

class Message
{
    protected Entry $entry;

    protected string $key;

    protected array $params = [];

    public function __construct(Entry $entry, string $key, array $params = [])
    {
        $this->entry = $entry;
        $this->key = $key;
        $this->params = $params;
    }

    public function getEntry(): Entry
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
}
