<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Interfaces\RuleSetGroupInterface;

class RuleSetGroup implements RuleSetGroupInterface
{
    /**
     * @var array<\DonnySim\Validation\RuleSet>
     */
    protected array $rules = [];

    /**
     * @param array<\DonnySim\Validation\RuleSet> $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param array<\DonnySim\Validation\RuleSet> $rules
     */
    public static function make(array $rules): self
    {
        return new self($rules);
    }

    /**
     * @return array<\DonnySim\Validation\RuleSet>
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
