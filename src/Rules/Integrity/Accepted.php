<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use function in_array;

final class Accepted implements RuleInterface
{
    public const NAME = 'accepted';

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent() || !in_array($entry->getValue(), [true, 'true', 1, '1', 'yes', 'on'], true)) {
            $process->fail(Message::forEntry($entry, self::NAME));
        }
    }
}
