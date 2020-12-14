<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\FieldReference;

class DateBeforeOrAfter implements SingleRule
{
    public const NAME_BEFORE = 'date_before';
    public const NAME_AFTER = 'date_after';

    /**
     * @var mixed
     */
    protected $date;

    protected ?string $format;

    protected string $operator;

    public function __construct($date, string $operator, ?string $format = null)
    {
        $this->date = $date;
        $this->operator = $operator;
        $this->format = $format;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $referenceValue = $this->date;
        if ($referenceValue instanceof FieldReference) {
            $referenceValue = $pipeline->getValidator()->getValueEntry($entry->resolvePathWildcards($referenceValue->getField()))->getValue();
        }

        if (!$referenceValue) {
            return;
        }

        $value = $entry->getValue();
        if (!\is_string($value) && !\is_numeric($value) && !$value instanceof DateTimeInterface) {
            $pipeline->fail($this->getMessageKey(), ['other' => $this->getDateString($referenceValue)]);
            return;
        }

        $format = $this->format;
        if (!$format) {
            /** @var \DonnySim\Validation\Rules\DateFormat|null $dateFormatRule */
            $dateFormatRule = $pipeline->findPreviousRule(DateFormat::class);

            if ($dateFormatRule) {
                $format = $dateFormatRule->getFormat();
            }
        }

        $second = $this->getDateTimeWithOptionalFormat($referenceValue, $format);

        if ($this->compare($this->getDateTimeWithOptionalFormat($value, $format), $second)) {
            return;
        }

        if (!$second && $this->date instanceof FieldReference) {
            $pipeline->fail($this->getMessageKey(), ['other' => $this->date->getField(), 'format' => $format]);
            return;
        }

        $pipeline->fail($this->getMessageKey(), ['other' => $this->getDateString($referenceValue, $format), 'format' => $format]);
    }

    protected function getDateTimeWithOptionalFormat($value, ?string $format = null): ?Carbon
    {
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        }

        try {
            return Carbon::createFromFormat($format, $value);
        } catch (InvalidFormatException $e) {
            return $this->getDateTime($value);
        }
    }

    protected function getDateTime($value, ?string $format = null): ?Carbon
    {
        try {
            if ($format) {
                return Carbon::createFromFormat($format, Carbon::parse($value)->format($format));
            }

            return Carbon::parse($value);
        } catch (InvalidFormatException $e) {
            return null;
        }
    }

    protected function compare(?Carbon $first, ?Carbon $second): bool
    {
        switch ($this->operator) {
            case '<':
                return $first < $second;
            case '>':
                return $first > $second;
            case '<=':
                return $first <= $second;
            case '>=':
                return $first >= $second;
            case '=':
                return $first == $second;
            default:
                throw new InvalidArgumentException('Unsupported date comparison operator.');
        }
    }

    protected function getDateString($value, ?string $format = null): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if ($format) {
            return Carbon::parse($value)->format($format);
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    protected function getMessageKey(): string
    {
        switch ($this->operator) {
            case '<':
                return static::NAME_BEFORE;
            case '>':
                return static::NAME_AFTER;
//            case '<=':
//                return $first <= $second;
//            case '>=':
//                return $first >= $second;
//            case '=':
//                return $first == $second;
            default:
                throw new InvalidArgumentException('Unsupported date comparison operator.');
        }
    }
}
