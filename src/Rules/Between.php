<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Between implements SingleRule
{
    public const NAME_STRING = 'between.string';
    public const NAME_ARRAY = 'between.array';
    public const NAME_NUMERIC = 'between.numeric';

    protected BigDecimal $min;

    protected BigDecimal $max;

    /**
     * @param int|float|string $min
     * @param int|float|string $max
     */
    public function __construct($min, $max)
    {
        $this->min = BigDecimal::of($min);
        $this->max = BigDecimal::of($max);
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if ($value === null) {
            $pipeline->fail(static::NAME_STRING, ['min' => $this->min, 'max' => $this->max]);
            return;
        }

        if ($pipeline->findPreviousRule(Numeric::class)) {
            if ($this->min->isGreaterThan($value) || $this->max->isLessThan($value)) {
                $pipeline->fail(static::NAME_NUMERIC, ['min' => $this->min, 'max' => $this->max]);
            }

            return;
        }

        if (\is_int($value) || \is_float($value)) {
            if ($this->min->isGreaterThan($value) || $this->max->isLessThan($value)) {
                $pipeline->fail(static::NAME_NUMERIC, ['min' => $this->min, 'max' => $this->max]);
            }

            return;
        }

        if (\is_string($value)) {
            $length = \mb_strlen($value);

            if ($this->min->isGreaterThan($length) || $this->max->isLessThan($length)) {
                $pipeline->fail(static::NAME_STRING, ['min' => $this->min, 'max' => $this->max]);
            }

            return;
        }

        if (\is_array($value)) {
            $size = \count($value);

            if ($this->min->isGreaterThan($size) || $this->max->isLessThan($size)) {
                $pipeline->fail(static::NAME_ARRAY, ['min' => $this->min, 'max' => $this->max]);
            }

            return;
        }

        $pipeline->fail(static::NAME_STRING, ['min' => $this->min, 'max' => $this->max]);
    }
}
