<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

use DonnySim\Validation\Data\Message;

interface MessageResolver
{
    /**
     * @param \DonnySim\Validation\Data\Message $message
     * @param \DonnySim\Validation\Contracts\MessageOverrideProvider $provider
     *
     * @return mixed
     */
    public function resolve(Message $message, MessageOverrideProvider $provider);
}
