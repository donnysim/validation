<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use function is_string;

class StringType implements SingleRule
{
    public const NAME = 'string_type';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing() || is_string($entry->getValue())) {
            return;
        }

        $pipeline->fail(static::NAME);
    }
}
