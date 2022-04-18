<?php

declare(strict_types=1);

namespace DonnySim\Validation;

final class Reference
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public static function make(self|string $field): self
    {
        if ($field instanceof self) {
            return $field;
        }

        return new self($field);
    }

    public function getField(): string
    {
        return $this->field;
    }
}
