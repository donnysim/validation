<?php

declare(strict_types=1);

namespace DonnySim\Validation\Interfaces;

interface MessageResolverInterface
{
    /**
     * @param array<\DonnySim\Validation\Message> $messages
     */
    public function resolveMessages(array $messages, MessageOverrideProviderInterface $overrideProvider): mixed;
}
