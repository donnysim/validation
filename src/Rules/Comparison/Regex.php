<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function is_string;
use function preg_match;

class Regex implements SingleRule
{
    public const NAME = 'regex';
    public const NAME_NOT = 'not_regex';

    protected string $pattern;

    protected bool $boolean;

    public function __construct(string $pattern, bool $boolean)
    {
        $this->pattern = $pattern;
        $this->boolean = $boolean;
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value)) {
            $entry->addMessageAndFinish($this->boolean ? static::NAME : static::NAME_NOT);
            return;
        }

        $result = preg_match($this->pattern, $value);

        if (($this->boolean && !$result) || (!$this->boolean && $result)) {
            $entry->addMessageAndFinish($this->boolean ? static::NAME : static::NAME_NOT);
        }
    }
}
