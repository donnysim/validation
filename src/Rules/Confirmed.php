<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Confirmed implements SingleRule
{
    public const NAME = 'confirmed';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $reference = $pipeline->getValidator()->getValueEntry($entry->getPath() . '_confirmation');

        if ($reference->isMissing() || $entry->getValue() !== $reference->getValue()) {
            $pipeline->fail(static::NAME);
        }
    }
}
