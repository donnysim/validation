<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Type;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use function is_string;

final class StringType implements RuleInterface
{
    public const NAME = 'string_type';

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent() || is_string($entry->getValue())) {
            return;
        }

        $process->fail(Message::forEntry($entry, self::NAME));
    }
}
