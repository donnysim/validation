<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

use DonnySim\Validation\Message;

interface MessageResolver
{
    public function resolve(Message $message): string;

    /**
     * Replace attribute paths with custom names.
     * Format ['pattern' => 'name'].
     *
     * @param array $attributes
     */
    public function setAttributeNames(array $attributes): void;
}
