<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Casts;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\ValidationProcess;
use function is_array;
use function is_bool;
use function is_string;

final class ToString implements RuleInterface
{
    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent() || is_string($entry->getValue())) {
            return;
        }

        $value = $entry->getValue();

        if (is_bool($value)) {
            $entry->setValue($value ? 'true' : 'false');
        } elseif (is_array($value)) {
            $entry->setValue('array');
        } else {
            $entry->setValue((string)$entry->getValue());
        }
    }
}
