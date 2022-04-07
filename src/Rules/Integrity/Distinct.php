<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\CleanupStateInterface;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;

final class Distinct implements RuleInterface, CleanupStateInterface
{
    public const NAME = 'distinct';

    private array $valueCache = [];

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        if (in_array($entry->getValue(), $this->valueCache, true)) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));

            return;
        }

        $this->valueCache[$entry->getPath()] = $entry->getValue();
    }

    public function cleanup(): void
    {
        $this->valueCache = [];
    }
}
