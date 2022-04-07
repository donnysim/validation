<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use function is_string;
use function preg_match;

final class AlphaDash implements RuleInterface
{
    public const NAME = 'alpha_dash';

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value) || !preg_match('/^[\pL\pM\pN_-]+$/u', $value)) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));
        }
    }
}
