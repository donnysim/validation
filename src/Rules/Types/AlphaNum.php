<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function is_string;
use function preg_match;

class AlphaNum implements SingleRule
{
    public const NAME = 'alpha_num';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value) || !preg_match('/^[\pL\pM\pN]+$/u', $value)) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
