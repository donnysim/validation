<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Between implements SingleRule
{
    public const NAME_STRING = 'between.string';
    public const NAME_ARRAY = 'between.array';
    public const NAME_NUMERIC = 'between.numeric';

    /**
     * @var int|float
     */
    protected $min;

    /**
     * @var int|float
     */
    protected $max;

    public function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
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

//        if (\is_numeric($value)) {
//            if ($value < $this->min) {
//                $pipeline->fail(static::NAME_NUMERIC, ['min' => $this->min, 'max' => $this->max]);
//            }
//
//            return;
//        }

        if (\is_int($value)) {
            if ($value < $this->min || $value > $this->max) {
                $pipeline->fail(static::NAME_NUMERIC, ['min' => $this->min, 'max' => $this->max]);
            }

            return;
        }

        if (\is_string($value)) {
            $length = \mb_strlen($value);

            if ($length < $this->min || $length > $this->max) {
                $pipeline->fail(static::NAME_STRING, ['min' => $this->min, 'max' => $this->max]);
            }

            return;
        }

        if (\is_array($value)) {
            $size = \count($value);

            if ($size < $this->min || $size > $this->max) {
                $pipeline->fail(static::NAME_ARRAY, ['min' => $this->min, 'max' => $this->max]);
            }

            return;
        }
    }
}
