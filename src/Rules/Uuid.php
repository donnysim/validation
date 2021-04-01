<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use Illuminate\Support\Str;

class Uuid implements SingleRule
{
    public const NAME = 'uuid';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (!Str::isUuid($entry->getValue())) {
            $pipeline->fail(static::NAME);
        }
    }
}
