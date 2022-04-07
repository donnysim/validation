<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\Rules\Traits\EmptyCheckTrait;

final class Filled implements RuleInterface
{
    use EmptyCheckTrait;

    public const NAME = 'filled';

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        if ($this->isEmpty($entry->getValue())) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));
        }
    }
}
