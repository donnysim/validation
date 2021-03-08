<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use function in_array;
use function timezone_identifiers_list;

class Timezone implements SingleRule
{
    public const NAME = 'timezone';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (!in_array($entry->getValue(), timezone_identifiers_list(), true)) {
            $pipeline->fail(static::NAME);
        }
    }
}
