<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Accepted implements SingleRule
{
    public const NAME = 'accepted';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing() || !\in_array($entry->getValue(), [true, 'true', 1, '1', 'yes', 'on'], true)) {
            $pipeline->fail(static::NAME);
        }
    }
}
