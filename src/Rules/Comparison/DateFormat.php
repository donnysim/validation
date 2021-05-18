<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use DateTime;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function is_string;

class DateFormat implements SingleRule
{
    public const NAME = 'date_format';

    protected string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if (!is_string($value)) {
            $entry->addMessageAndFinish(static::NAME, ['format' => $this->format]);
            return;
        }

        $date = DateTime::createFromFormat('!' . $this->format, $entry->getValue());
        if (!$date || $date->format($this->format) !== $entry->getValue()) {
            $entry->addMessageAndFinish(static::NAME, ['format' => $this->format]);
        }
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
