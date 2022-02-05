<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Interfaces\MessageResolverInterface;

class ArrayMessageResolver implements MessageResolverInterface
{
    public function resolveMessage(array $messages): array
    {
        $result = [];

        foreach ($messages as $message) {
            $result[$message->getPath()][] = $message->jsonSerialize();
        }

        return $result;
    }
}
