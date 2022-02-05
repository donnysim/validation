<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use function is_array;
use function is_string;
use function trim;

final class Required implements RuleInterface
{
    public const NAME = 'required';

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent() || $this->isEmpty($entry->getValue())) {
            $process->fail(Message::forEntry($entry, self::NAME));
        }
    }

    protected function isEmpty($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }
}
