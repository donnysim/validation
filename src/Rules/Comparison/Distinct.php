<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use DonnySim\Validation\Contracts\BatchRule;
use DonnySim\Validation\Process\ValidationProcess;
use function array_search;

class Distinct implements BatchRule
{
    public const NAME = 'distinct';

    public function handle(ValidationProcess $process, array $entries): void
    {
        $uniqueValues = [];

        foreach ($entries as $entryIndex => $entry) {
            if ($entry->isMissing()) {
                continue;
            }

            $index = array_search($entry->getValue(), $uniqueValues, true);
            if ($index !== false) {
                $entries[$index]->addMessage(static::NAME);
                $entry->addMessage(static::NAME);
                continue;
            }

            $uniqueValues[$entryIndex] = $entry->getValue();
        }
    }
}
