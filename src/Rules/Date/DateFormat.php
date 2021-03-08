<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Date;

use DateTime;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class DateFormat implements SingleRule
{
    public const NAME = 'date_format';

    protected string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if (!\is_string($value)) {
            $pipeline->fail(static::NAME, ['format' => $this->format]);
            return;
        }

        $date = DateTime::createFromFormat('!' . $this->format, $entry->getValue());
        if (!$date || $date->format($this->format) !== $entry->getValue()) {
            $pipeline->fail(static::NAME, ['format' => $this->format]);
        }
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
