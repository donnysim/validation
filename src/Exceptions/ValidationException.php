<?php

declare(strict_types=1);

namespace DonnySim\Validation\Exceptions;

use DonnySim\Validation\Interfaces\MessageResolverInterface;
use DonnySim\Validation\Validator;
use Exception;

class ValidationException extends Exception
{
    private Validator $validator;

    public function __construct(Validator $validator)
    {
        parent::__construct('The given data was invalid.');
        $this->validator = $validator;
    }

    public function resolveMessages(?MessageResolverInterface $messageResolver = null): mixed
    {
        return $this->validator->resolveMessages($messageResolver);
    }
}
