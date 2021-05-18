<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;

class BooleanLike implements SingleRule
{
    public const NAME = 'boolean_like';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing() || $this->isBooleanLike($entry->getValue())) {
            return;
        }

        $entry->addMessageAndFinish(static::NAME);
    }

    protected function isBooleanLike($value): bool
    {
        return in_array($value, [true, false, 'true', 'false', 1, 0, '1', '0', 'yes', 'no', 'on', 'off'], true);
    }
}
