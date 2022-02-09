<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use function is_int;
use function is_string;
use function preg_match;

final class Digits implements RuleInterface
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

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $value = $entry->getValue();

        if (is_int($value)) {
            $value = (string)$value;
        }

        if ($this->operator === '=') {
            if (!is_string($value) || preg_match('/\D/', $value) || mb_strlen($value) !== $this->first) {
                $process->fail(Message::forEntry($entry, self::NAME, ['digits' => $this->first]));
            }

            return;
        }

        if ($this->operator === '><') {
            if (!is_string($value) || preg_match('/\D/', $value)) {
                $process->fail(Message::forEntry($entry, self::NAME_BETWEEN, ['min' => $this->first, 'max' => $this->second]));

                return;
            }

            $length = mb_strlen($value);
            if ($length < $this->first || $length > $this->second) {
                $process->fail(Message::forEntry($entry, self::NAME_BETWEEN, ['min' => $this->first, 'max' => $this->second]));
            }
        }
    }
}
