<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

interface SingleRule extends Rule
{
    public function handle(EntryPipeline $pipeline, Entry $entry): void;
}
