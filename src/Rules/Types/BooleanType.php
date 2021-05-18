<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function is_bool;

class BooleanType implements SingleRule
{
    public const NAME = 'boolean_type';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing() || is_bool($entry->getValue())) {
            return;
        }

        $entry->addMessageAndFinish(static::NAME);
    }
}
