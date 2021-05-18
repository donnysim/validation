<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;
use function timezone_identifiers_list;

class Timezone implements SingleRule
{
    public const NAME = 'timezone';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (!in_array($entry->getValue(), timezone_identifiers_list(), true)) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
