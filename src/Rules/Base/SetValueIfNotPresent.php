<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\ValidationProcess;

final class SetValueIfNotPresent implements RuleInterface
{
    protected mixed $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            $entry->setValue($this->value);
            $process->getCurrent()->setShouldExtractValue(true);
            $process->getCurrent()->stop();
        }
    }
}
