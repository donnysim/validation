<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

use DonnySim\Validation\Message;

interface MessageResolver
{
    public function resolve(Message $message): string;
}
