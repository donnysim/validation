<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Casts;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\EntryProcess;
use function is_string;

final class ToString implements RuleInterface
{
    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent() || is_string($entry->getValue())) {
            return;
        }

        $entry->setValue((string)$entry->getValue());
    }
}
