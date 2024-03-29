<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity\Date;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DateTime;
use DonnySim\Validation\Process\ValidationProcess;
use function is_string;

final class DateFormat implements RuleInterface
{
    public const NAME = 'date_format';

    protected string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $value = $entry->getValue();

        if (!is_string($value)) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME, ['format' => $this->format]));

            return;
        }

        $date = DateTime::createFromFormat('!' . $this->format, $entry->getValue());
        if (!$date || $date->format($this->format) !== $entry->getValue()) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME, ['format' => $this->format]));
        }
    }
}
