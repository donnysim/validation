<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;

final class Confirmed implements RuleInterface
{
    public const NAME = 'confirmed';

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $reference = $process->getEntry($entry->getPath() . '_confirmation');

        if ($reference->isNotPresent() || $entry->getValue() !== $reference->getValue()) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));
        }
    }
}
