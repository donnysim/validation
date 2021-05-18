<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;

class Required implements SingleRule
{
    public const NAME = 'required';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing() || empty($entry->getValue())) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
