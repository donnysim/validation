<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity\Date;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DateTime;
use DateTimeInterface;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\Reference;
use InvalidArgumentException;
use function is_numeric;
use function is_string;

final class DateComparison implements RuleInterface
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

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $referenceValue = $this->date;
        if ($referenceValue instanceof Reference) {
            $referenceValue = $process->getEntry($entry->resolveSegmentWildcards($referenceValue->getField()))->getValue();
        }

        if (!$referenceValue) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value) && !is_numeric($value) && !$value instanceof DateTimeInterface) {
            $process->getCurrent()->fail(Message::forEntry($entry, $this->getMessageKey(), ['date' => $this->getDateString($referenceValue)]));

            return;
        }

        $format = $this->format;
        if (!$format) {
            $dateFormatRule = $process->getCurrent()->findPreviousRule(DateFormat::class);

            if ($dateFormatRule) {
                $format = $dateFormatRule->getFormat();
            }
        }

        $second = $this->getDateTimeWithOptionalFormat($referenceValue, $format);

        if ($this->compare($this->getDateTimeWithOptionalFormat($value, $format), $second)) {
            return;
        }

        if (!$second && $this->date instanceof Reference) {
            $process->getCurrent()->fail(Message::forEntry($entry, $this->getMessageKey(), ['date' => $this->date->getField(), 'format' => $format]));

            return;
        }

        $process->getCurrent()->fail(Message::forEntry($entry, $this->getMessageKey(), ['date' => $this->getDateString($referenceValue, $format), 'format' => $format]));
    }

    protected function getDateTimeWithOptionalFormat($value, ?string $format = null): ?Carbon
    {
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        }

        try {
            return @Carbon::createFromFormat($format, $value);
        } catch (InvalidFormatException) {
            return $this->getDateTime($value, $format);
        }
    }

    protected function getDateTime($value, ?string $format = null): ?Carbon
    {
        try {
            if ($format) {
                return @Carbon::createFromFormat($format, Carbon::make($value)?->format($format));
            }

            return Carbon::make($value);
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

    protected function getDateString($value, ?string $format = null): string
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
            '<' => self::NAME_BEFORE,
            '>' => self::NAME_AFTER,
            '<=' => self::NAME_BEFORE_OR_EQUAL,
            '>=' => self::NAME_AFTER_OR_EQUAL,
            '=' => self::NAME_EQUAL,
            default => throw new InvalidArgumentException('Unsupported date comparison operator.'),
        };
    }
}
