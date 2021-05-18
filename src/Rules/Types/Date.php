<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DateTimeInterface;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function checkdate;
use function date_parse;
use function is_numeric;
use function is_string;
use function strtotime;

class Date implements SingleRule
{
    public const NAME = 'date';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if ($value instanceof DateTimeInterface) {
            return;
        }

        if ((!is_string($value) && !is_numeric($value)) || strtotime($value) === false) {
            $entry->addMessageAndFinish(static::NAME);
            return;
        }

        $date = date_parse($value);
        if (!$date || $date['month'] === false || $date['day'] === false || $date['year'] === false) {
            $entry->addMessageAndFinish(static::NAME);
            return;
        }

        if (!checkdate($date['month'], $date['day'], $date['year'])) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
