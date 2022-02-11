<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use DonnySim\Validation\Rules\Traits\SizeValidationTrait;
use DonnySim\Validation\Rules\Type\Numeric;

final class Between implements RuleInterface
{
    use SizeValidationTrait;

    public const NAME = 'between';

    protected ?BigDecimal $min;

    protected ?BigDecimal $max;

    public function __construct(float|int|string $min, float|int|string $max)
    {
        $this->min = $this->getValueSize($min, true);
        $this->max = $this->getValueSize($max, true);
    }

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $numeric = $process->findPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if (
            $value === null
            || $this->min === null
            || $this->max === null
            || $this->min->isGreaterThan($value)
            || $this->max->isLessThan($value)
        ) {
            $process->fail(
                Message::forEntry(
                    $entry,
                    $this->messageKey($entry->getValue(), $numeric),
                    ['min' => $this->valueForError($this->min), 'max' => $this->valueForError($this->max)]
                )
            );
        }
    }

    protected function messageKey($value, bool $canBeNumeric): string
    {
        return self::NAME . '.' . $this->getValueType($value, $canBeNumeric);
    }
}
