<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use function is_int;
use function is_string;
use function mb_strlen;
use function preg_match;

class Digits implements SingleRule
{
    public const NAME = 'digits';
    public const NAME_BETWEEN = 'digits_between';

    protected int $first;

    protected int $second;

    protected string $operator;

    public function __construct(string $operator, int $first, int $second = 0)
    {
        $this->operator = $operator;
        $this->first = $first;
        $this->second = $second;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if (is_int($value)) {
            $value = (string)$value;
        }

        if ($this->operator === '=') {
            if (!is_string($value) || preg_match('/\D/', $value) || mb_strlen($value) !== $this->first) {
                $pipeline->fail(static::NAME, ['digits' => $this->first]);
            }

            return;
        }

        if ($this->operator === '><') {
            if (!is_string($value) || preg_match('/\D/', $value)) {
                $pipeline->fail(static::NAME_BETWEEN, ['min' => $this->first, 'max' => $this->second]);
                return;
            }

            $length = mb_strlen($value);
            if ($length < $this->first || $length > $this->second) {
                $pipeline->fail(static::NAME_BETWEEN, ['min' => $this->first, 'max' => $this->second]);
            }
        }
    }
}
