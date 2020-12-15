<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Contracts\MessageResolver;

class ValidatorFactory
{
    protected MessageResolver $resolver;

    public function __construct(MessageResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function make(array $data, array $rules, array $attributeNames = []): Validator
    {
        return new Validator($this->resolver, $data, $rules, $attributeNames);
    }
}
