<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;

class Sometimes implements SingleRule
{
    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            $entry->finish();
        }
    }
}
