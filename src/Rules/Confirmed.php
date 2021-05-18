<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;

class Confirmed implements SingleRule
{
    public const NAME = 'confirmed';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $reference = $process->getDataEntry($entry->getPath() . '_confirmation');

        if ($reference->isMissing() || $entry->getValue() !== $reference->getValue()) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
