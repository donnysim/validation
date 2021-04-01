<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Core;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;

class Nested implements SingleRule
{
    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
//        $pipeline->getValidator()->
    }
}
