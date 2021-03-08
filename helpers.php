<?php

declare(strict_types=1);

namespace DonnySim\Validation;

function validator(array $data, array $rules, array $overrides = []): Validator
{
    return ValidatorFactory::instance()->make($data, $rules, $overrides);
}

function reference(string $pattern): FieldReference
{
    return Rules::reference($pattern);
}

