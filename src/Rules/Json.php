<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Json implements SingleRule
{
    public const NAME = 'json';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        \json_decode($entry->getValue(), false);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            $pipeline->fail(static::NAME);
        }
    }
}
