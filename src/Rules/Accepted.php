<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;

class Accepted implements SingleRule
{
    public const NAME = 'accepted';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing() || !in_array($entry->getValue(), [true, 'true', 1, '1', 'yes', 'on'], true)) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
