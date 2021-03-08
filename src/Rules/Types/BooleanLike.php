<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use function in_array;

class BooleanLike implements SingleRule
{
    public const NAME = 'boolean_like';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing() || $this->isBooleanLike($entry->getValue())) {
            return;
        }

        $pipeline->fail(static::NAME);
    }

    protected function isBooleanLike($value): bool
    {
        return in_array($value, [true, false, 'true', 'false', 1, 0, '1', '0', 'yes', 'no', 'on', 'off'], true);
    }
}
