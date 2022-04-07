<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\ValidationProcess;

final class Optional implements RuleInterface
{
    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            $process->getCurrent()->stop();
        }
    }
}
