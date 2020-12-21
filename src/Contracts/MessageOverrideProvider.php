<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

interface MessageOverrideProvider
{
    public function getMessageOverrides(): array;
}
