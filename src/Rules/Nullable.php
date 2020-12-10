<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Nullable implements SingleRule
{
    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if (!$entry->isMissing() && $entry->getValue() === null) {
            $pipeline->finish();
        }
    }
}
