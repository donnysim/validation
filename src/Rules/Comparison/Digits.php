<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
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

    public function handle(ValidationProcess $process, DataEntry $entry): void
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
                $entry->addMessageAndFinish(static::NAME, ['digits' => $this->first]);
            }

            return;
        }

        if ($this->operator === '><') {
            if (!is_string($value) || preg_match('/\D/', $value)) {
                $entry->addMessageAndFinish(static::NAME_BETWEEN, ['min' => $this->first, 'max' => $this->second]);
                return;
            }

            $length = mb_strlen($value);
            if ($length < $this->first || $length > $this->second) {
                $entry->addMessageAndFinish(static::NAME_BETWEEN, ['min' => $this->first, 'max' => $this->second]);
            }
        }
    }
}
