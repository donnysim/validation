<?php

declare(strict_types=1);

namespace DonnySim\Validation\Interfaces;

use DonnySim\Validation\Message;

interface MessageOverrideProviderInterface
{
    public function getMessageOverride(Message $message): ?string;

    public function getAttributeOverride(Message $message): string;
}
