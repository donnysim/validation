<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Interfaces\MessageOverrideProviderInterface;
use DonnySim\Validation\Interfaces\MessageResolverInterface;

class ArrayMessageResolver implements MessageResolverInterface
{
    public function resolveMessages(array $messages, MessageOverrideProviderInterface $overrideProvider): array
    {
        $result = [];

        foreach ($messages as $message) {
            $result[$message->getPath()][] = $message->jsonSerialize();
        }

        return $result;
    }
}
