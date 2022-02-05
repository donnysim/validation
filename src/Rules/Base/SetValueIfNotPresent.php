<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\EntryProcess;

final class SetValueIfNotPresent implements RuleInterface
{
    protected mixed $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            $entry->setValue($this->value);
            $process->setShouldExtractValue(true);
            $process->stop();
        }
    }
}
