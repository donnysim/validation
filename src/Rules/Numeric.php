<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Numeric implements SingleRule
{
    public const NAME = 'numeric';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (!\is_numeric($entry->getValue())) {
            $pipeline->fail(static::NAME);
        }
    }
}
