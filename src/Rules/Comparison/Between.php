<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\Rules\Concerns\SizeValidation;
use DonnySim\Validation\Rules\Types\Numeric;

class Between implements SingleRule
{
    use SizeValidation;

    public const NAME = 'between';

    protected ?BigDecimal $min;

    protected ?BigDecimal $max;

    public function __construct(mixed $min, mixed $max)
    {
        $this->min = $this->getValueSize($min, true);
        $this->max = $this->getValueSize($max, true);
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $numeric = $entry->getPipeline()->getPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if ($value === null || $this->min === null || $this->max === null || $this->min->isGreaterThan($value) || $this->max->isLessThan($value)) {
            $entry->addMessageAndFinish($this->messageKey($entry->getValue(), $numeric), ['min' => $this->valueForError($this->min), 'max' => $this->valueForError($this->max)]);
        }
    }

    protected function messageKey(mixed $value, bool $canBeNumeric): string
    {
        return static::NAME . '.' . $this->getValueType($value, $canBeNumeric);
    }
}
