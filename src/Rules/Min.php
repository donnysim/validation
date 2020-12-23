<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules\Concerns\SizeValidation;

class Min implements SingleRule
{
    use SizeValidation;

    public const NAME = 'min';

    protected ?BigDecimal $min;

    /**
     * @param int|float|string $min
     */
    public function __construct($min)
    {
        $this->min = $this->getValueSize($min, true);
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $numeric = $pipeline->findPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if ($value === null || $this->min === null || $this->min->isGreaterThan($value)) {
            $pipeline->fail($this->messageKey($entry->getValue(), $numeric), ['min' => $this->valueForError($this->min)]);
            return;
        }
    }

    protected function messageKey($value, bool $canBeNumeric): string
    {
        return static::NAME . '.' . $this->getValueType($value, $canBeNumeric);
    }
}
