<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTime;
use DateTimeInterface;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\Reference;
use InvalidArgumentException;
use function is_numeric;
use function is_string;

class DateBeforeOrAfter implements SingleRule
{
    public const NAME_BEFORE = 'date_before';
    public const NAME_BEFORE_OR_EQUAL = 'date_before_or_equal';
    public const NAME_AFTER = 'date_after';
    public const NAME_AFTER_OR_EQUAL = 'date_after_or_equal';
    public const NAME_EQUAL = 'date_equal';

    protected mixed $date;

    protected ?string $format;

    protected string $operator;

    public function __construct(mixed $date, string $operator, ?string $format = null)
    {
        $this->date = $date;
        $this->operator = $operator;
        $this->format = $format;
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $referenceValue = $this->date;
        if ($referenceValue instanceof Reference) {
            $referenceValue = $process->getOriginalDataEntry($entry->resolveSegmentWildcards($referenceValue->getField()))->getValue();
        }

        if (!$referenceValue) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value) && !is_numeric($value) && !$value instanceof DateTimeInterface) {
            $entry->addMessageAndFinish($this->getMessageKey(), ['date' => $this->getDateString($referenceValue)]);
            return;
        }

        $format = $this->format;
        if (!$format) {
            /** @var \DonnySim\Validation\Rules\Comparison\DateFormat|null $dateFormatRule */
            $dateFormatRule = $entry->getPipeline()->getPreviousRule(DateFormat::class);

            if ($dateFormatRule) {
                $format = $dateFormatRule->getFormat();
            }
        }

        $second = $this->getDateTimeWithOptionalFormat($referenceValue, $format);

        if ($this->compare($this->getDateTimeWithOptionalFormat($value, $format), $second)) {
            return;
        }

        if (!$second && $this->date instanceof Reference) {
            $entry->addMessageAndFinish($this->getMessageKey(), ['date' => $this->date->getField(), 'format' => $format]);
            return;
        }

        $entry->addMessageAndFinish($this->getMessageKey(), ['date' => $this->getDateString($referenceValue, $format), 'format' => $format]);
    }

    protected function getDateTimeWithOptionalFormat(mixed $value, ?string $format = null): ?Carbon
    {
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        }

        try {
            return Carbon::createFromFormat($format, $value);
        } catch (InvalidFormatException) {
            return $this->getDateTime($value, $format);
        }
    }

    protected function getDateTime(mixed $value, ?string $format = null): ?Carbon
    {
        try {
            if ($format) {
                return Carbon::createFromFormat($format, Carbon::parse($value)->format($format));
            }

            return Carbon::parse($value);
        } catch (InvalidFormatException) {
            return null;
        }
    }

    protected function compare(?Carbon $first, ?Carbon $second): bool
    {
        return match ($this->operator) {
            '<' => $first < $second,
            '>' => $first > $second,
            '<=' => $first <= $second,
            '>=' => $first >= $second,
            '=' => $first == $second,
            default => throw new InvalidArgumentException('Unsupported date comparison operator.'),
        };
    }

    protected function getDateString(mixed $value, ?string $format = null): string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($format) {
            return Carbon::parse($value)->format($format);
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    protected function getMessageKey(): string
    {
        return match ($this->operator) {
            '<' => static::NAME_BEFORE,
            '>' => static::NAME_AFTER,
            '<=' => static::NAME_BEFORE_OR_EQUAL,
            '>=' => static::NAME_AFTER_OR_EQUAL,
            '=' => static::NAME_EQUAL,
            default => throw new InvalidArgumentException('Unsupported date comparison operator.'),
        };
    }
}
