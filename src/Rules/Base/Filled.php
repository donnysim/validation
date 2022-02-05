<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;

final class Filled implements RuleInterface
{
    public const NAME = 'filled';

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        if (empty($entry->getValue())) {
            $process->fail(Message::forEntry($entry, self::NAME));
        }
    }
}
