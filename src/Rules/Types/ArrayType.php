<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function is_array;

class ArrayType implements SingleRule
{
    public const NAME = 'array_type';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (!is_array($entry->getValue())) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
