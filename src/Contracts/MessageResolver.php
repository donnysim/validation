<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

interface MessageResolver
{
    /**
     * @param \DonnySim\Validation\Process\ValidationMessage[] $messages
     *
     * @return mixed
     */
    public function resolve(array $messages): mixed;
}
