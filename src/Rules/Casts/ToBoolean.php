<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Casts;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;
use function is_bool;

final class ToBoolean implements RuleInterface
{
    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent() || is_bool($entry->getValue())) {
            return;
        }

        $entry->setValue(in_array($entry->getValue(), ['true', 1, '1', 'yes', 'on'], true));
    }
}
