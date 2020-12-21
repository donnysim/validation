<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Min implements SingleRule
{
    public const NAME_STRING = 'min.string';
    public const NAME_ARRAY = 'min.array';
    public const NAME_NUMERIC = 'min.numeric';

    /**
     * @var int|float|string
     */
    protected $min;

    /**
     * @param int|float $min
     */
    public function __construct($min)
    {
        $this->min = $min;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if ($value === null) {
            $pipeline->fail(static::NAME_STRING, ['min' => $this->min]);
            return;
        }

        if ($pipeline->findPreviousRule(Numeric::class)) {
            $decimal = BigDecimal::of($this->min);

            if ($decimal->isGreaterThan($value)) {
                $pipeline->fail(static::NAME_NUMERIC, ['min' => $decimal]);
            }

            return;
        }

        if (\is_int($value)) {
            if ($value < $this->min) {
                $pipeline->fail(static::NAME_NUMERIC, ['min' => $this->min]);
            }

            return;
        }

        if (\is_float($value)) {
            $decimal = BigDecimal::of($this->min);

            if ($decimal->isGreaterThan($value)) {
                $pipeline->fail(static::NAME_NUMERIC, ['min' => $decimal]);
            }

            return;
        }

        if (\is_string($value)) {
            if (\mb_strlen($value) < $this->min) {
                $pipeline->fail(static::NAME_STRING, ['min' => $this->min]);
            }

            return;
        }

        if (\is_array($value)) {
            if (\count($value) < $this->min) {
                $pipeline->fail(static::NAME_ARRAY, ['min' => $this->min]);
            }

            return;
        }

        $pipeline->fail(static::NAME_STRING, ['min' => $this->min]);
    }
}
