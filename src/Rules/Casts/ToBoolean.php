<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Casts;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;
use function is_bool;

class ToBoolean implements SingleRule
{
    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing() || is_bool($entry->getValue())) {
            return;
        }

        $entry->setValue(in_array($entry->getValue(), ['true', 1, '1', 'yes', 'on'], true));
    }
}
