<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

use DonnySim\Validation\Message;

interface MessageResolver
{
    /**
     * @param \DonnySim\Validation\Message $message
     * @param \DonnySim\Validation\Contracts\MessageOverrideProvider $provider
     *
     * @return string
     */
    public function resolve(Message $message, MessageOverrideProvider $provider): string;
}
