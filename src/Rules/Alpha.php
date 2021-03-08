<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use function is_string;
use function preg_match;

class Alpha implements SingleRule
{
    public const NAME = 'alpha';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value) || !preg_match('/^[\pL\pM]+$/u', $value)) {
            $pipeline->fail(static::NAME);
        }
    }
}
