<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Contracts\MessageResolver;
use UnexpectedValueException;

class ValidatorFactory
{
    protected static ?self $instance = null;

    protected MessageResolver $resolver;

    public function __construct(MessageResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public static function setInstance(self $instance): void
    {
        static::$instance = $instance;
    }

    public static function instance(): self
    {
        if (!static::$instance) {
            throw new UnexpectedValueException('Global instance has not been set. Use setInstance to set global instance.');
        }

        return static::$instance;
    }

    public function make(array $data, array $rules, array $attributeNames = []): Validator
    {
        $validator = new Validator($this->resolver, $data, $rules);
        $validator->override($attributeNames);

        return $validator;
    }
}
