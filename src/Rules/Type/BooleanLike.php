<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Type;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;

final class BooleanLike implements RuleInterface
{
    public const NAME = 'boolean_like';

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent() || $this->isBooleanLike($entry->getValue())) {
            return;
        }

        $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));
    }

    protected function isBooleanLike(mixed $value): bool
    {
        return in_array($value, [true, false, 'true', 'false', 1, 0, '1', '0', 'yes', 'no', 'on', 'off'], true);
    }
}
