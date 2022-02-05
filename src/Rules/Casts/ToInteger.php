<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Casts;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\EntryProcess;
use function is_int;

final class ToInteger implements RuleInterface
{
    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent() || is_int($entry->getValue())) {
            return;
        }

        $entry->setValue((int)$entry->getValue());
    }
}
