<?php

declare(strict_types=1);

namespace DonnySim\Validation\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected mixed $messages;

    public function __construct(mixed $messages)
    {
        parent::__construct('The given data was invalid.');
        $this->messages = $messages;
    }

    public function getMessages(): mixed
    {
        return $this->messages;
    }
}
