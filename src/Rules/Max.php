<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules\Concerns\SizeValidation;

class Max implements SingleRule
{
    use SizeValidation;

    public const NAME = 'max';

    protected ?BigDecimal $max;

    /**
     * @param int|float|string $max
     */
    public function __construct($max)
    {
        $this->max = $this->getValueSize($max, true);
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $numeric = $pipeline->findPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if ($value === null || $this->max === null || $this->max->isLessThan($value)) {
            $pipeline->fail($this->messageKey($entry->getValue(), $numeric), ['max' => $this->valueForError($this->max)]);
            return;
        }
    }

    protected function messageKey($value, bool $canBeNumeric): string
    {
        return static::NAME . '.' . $this->getValueType($value, $canBeNumeric);
    }
}
