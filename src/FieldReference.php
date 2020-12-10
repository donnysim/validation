<?php

declare(strict_types=1);

namespace DonnySim\Validation;

class FieldReference
{
    protected string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function getField(): string
    {
        return $this->field;
    }
}
