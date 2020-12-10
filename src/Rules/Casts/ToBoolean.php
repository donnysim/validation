<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Casts;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class ToBoolean implements SingleRule
{
    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing() || \is_bool($entry->getValue())) {
            return;
        }

        $entry->setValue(\in_array($entry->getValue(), ['true', 1, '1', 'yes', 'on'], true));
    }
}
