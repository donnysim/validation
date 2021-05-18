<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;

class Filled implements SingleRule
{
    public const NAME = 'filled';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (empty($entry->getValue())) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
