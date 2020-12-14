<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Max implements SingleRule
{
    public const NAME_STRING = 'max.string';
    public const NAME_ARRAY = 'max.array';
    public const NAME_NUMERIC = 'max.numeric';

    protected int $max;

    public function __construct(int $max)
    {
        $this->max = $max;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

//        if ($value === null) {
//            $pipeline->fail(static::NAME, ['max' => $this->max]);
//            return;
//        }

//        if (\is_numeric($value)) {
//            if ($value > $this->max) {
//                $pipeline->fail(static::NAME_NUMERIC, ['max' => $this->max]);
//            }
//
//            return;
//        }

        if (\is_int($value)) {
            if ($value > $this->max) {
                $pipeline->fail(static::NAME_NUMERIC, ['max' => $this->max]);
            }

            return;
        }

        if (\is_string($value)) {
            if (\mb_strlen($value) > $this->max) {
                $pipeline->fail(static::NAME_STRING, ['max' => $this->max]);
            }

            return;
        }

        if (\is_array($value)) {
            if (\count($value) > $this->max) {
                $pipeline->fail(static::NAME_ARRAY, ['max' => $this->max]);
            }

            return;
        }
    }
}
