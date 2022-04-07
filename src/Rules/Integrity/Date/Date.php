<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity\Date;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DateTimeInterface;
use DonnySim\Validation\Process\ValidationProcess;
use function checkdate;
use function date_parse;
use function is_numeric;
use function is_string;
use function strtotime;

final class Date implements RuleInterface
{
    public const NAME = 'date';

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $value = $entry->getValue();
        if ($value instanceof DateTimeInterface) {
            return;
        }

        if ((!is_string($value) && !is_numeric($value)) || strtotime($value) === false) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));

            return;
        }

        $date = date_parse($value);
        if (!$date || $date['month'] === false || $date['day'] === false || $date['year'] === false) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));

            return;
        }

        if (!checkdate($date['month'], $date['day'], $date['year'])) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME));
        }
    }
}
