<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use function is_int;

class IntegerType implements SingleRule
{
    public const NAME = 'integer_type';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing() || is_int($entry->getValue())) {
            return;
        }

        $pipeline->fail(static::NAME);
    }
}
