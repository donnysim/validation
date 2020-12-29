<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Contracts\MessageResolver;
use UnexpectedValueException;

class ValidatorFactory
{
    protected static ?self $instance = null;

    protected static ?Closure $instanceResolver = null;

    protected MessageResolver $resolver;

    public function __construct(MessageResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public static function setInstance(self $instance): void
    {
        static::$instance = $instance;
    }

    public static function setInstanceResolver(Closure $closure): void
    {
        static::$instanceResolver = $closure;
    }

    public static function instance(): self
    {
        if (!static::$instance) {
            if (static::$instanceResolver) {
                static::$instance = (static::$instanceResolver)();
            } else {
                throw new UnexpectedValueException('Global instance has not been set. Use setInstance to set global instance.');
            }
        }

        return static::$instance;
    }

    public function make(array $data, array $rules, array $overrides = []): Validator
    {
        $validator = new Validator($this->resolver, $data, $rules);
        $validator->override($overrides);

        return $validator;
    }
}
