<?php

declare(strict_types=1);

namespace DonnySim\Validation\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidationException extends Exception
{
    protected MessageBag $messages;

    /**
     * @param \Illuminate\Support\MessageBag|array $messages
     */
    public function __construct($messages)
    {
        parent::__construct('The given data was invalid.');

        $this->messages = $messages instanceof MessageBag ? $messages : new MessageBag($messages);
    }
}
