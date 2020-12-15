<?php

declare(strict_types=1);

namespace DonnySim\Validation;

function rule(string $pattern, bool $includeInData = true): Rules
{
    return Rules::make($pattern, $includeInData);
}

function field_reference(string $pattern): FieldReference
{
    return Rules::reference($pattern);
}

