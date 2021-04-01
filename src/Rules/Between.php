<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use DonnySim\Validation\Rules\Concerns\SizeValidation;

class Between implements SingleRule
{
    use SizeValidation;

    public const NAME = 'between';

    protected ?BigDecimal $min;

    protected ?BigDecimal $max;

    /**
     * @param int|float|string $min
     * @param int|float|string $max
     */
    public function __construct($min, $max)
    {
        $this->min = $this->getValueSize($min, true);
        $this->max = $this->getValueSize($max, true);
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $numeric = $pipeline->findPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if ($value === null || $this->min === null || $this->max === null || $this->min->isGreaterThan($value) || $this->max->isLessThan($value)) {
            $pipeline->fail($this->messageKey($entry->getValue(), $numeric), ['min' => $this->valueForError($this->min), 'max' => $this->valueForError($this->max)]);
        }
    }

    protected function messageKey($value, bool $canBeNumeric): string
    {
        return static::NAME . '.' . $this->getValueType($value, $canBeNumeric);
    }
}
