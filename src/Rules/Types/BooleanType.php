<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use function is_bool;

class BooleanType implements SingleRule
{
    public const NAME = 'boolean_type';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing() || is_bool($entry->getValue())) {
            return;
        }

        $pipeline->fail(static::NAME);
    }
}
