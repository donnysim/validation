<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Date;

use DateTimeInterface;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use function checkdate;
use function date_parse;
use function is_numeric;
use function is_string;
use function strtotime;

class Date implements SingleRule
{
    public const NAME = 'date';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if ($value instanceof DateTimeInterface) {
            return;
        }

        if ((!is_string($value) && !is_numeric($value)) || strtotime($value) === false) {
            $pipeline->fail(static::NAME);
            return;
        }

        $date = date_parse($value);
        if (!$date || $date['month'] === false || $date['day'] === false || $date['year'] === false) {
            $pipeline->fail(static::NAME);
            return;
        }

        if (!checkdate($date['month'], $date['day'], $date['year'])) {
            $pipeline->fail(static::NAME);
        }
    }
}
