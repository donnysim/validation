<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;

class OmitFromData implements SingleRule
{
    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        $pipeline->omitFromData();
    }
}
