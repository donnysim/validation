<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;

class Filled implements SingleRule
{
    public const NAME = 'filled';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (empty($entry->getValue())) {
            $pipeline->fail(static::NAME);
        }
    }
}
