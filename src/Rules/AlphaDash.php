<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use function is_string;
use function preg_match;

class AlphaDash implements SingleRule
{
    public const NAME = 'alpha_dash';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value) || !preg_match('/^[\pL\pM\pN_-]+$/u', $value)) {
            $pipeline->fail(static::NAME);
        }
    }
}
