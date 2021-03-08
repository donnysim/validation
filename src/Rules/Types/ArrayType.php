<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use function is_array;

class ArrayType implements SingleRule
{
    public const NAME = 'array_type';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if (!$entry->isMissing() && !is_array($entry->getValue())) {
            $pipeline->fail(static::NAME);
        }
    }
}
