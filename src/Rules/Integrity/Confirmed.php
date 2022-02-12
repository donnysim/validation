<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;

final class Confirmed implements RuleInterface
{
    public const NAME = 'confirmed';

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $reference = $process->getFieldEntry($entry->getPath() . '_confirmation');

        if ($reference->isNotPresent() || $entry->getValue() !== $reference->getValue()) {
            $process->fail(Message::forEntry($entry, self::NAME));
        }
    }
}
