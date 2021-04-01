<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
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

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value)) {
            $pipeline->fail($this->boolean ? static::NAME : static::NAME_NOT);
            return;
        }

        $result = preg_match($this->pattern, $value);

        if (($this->boolean && !$result) || (!$this->boolean && $result)) {
            $pipeline->fail($this->boolean ? static::NAME : static::NAME_NOT);
        }
    }
}
