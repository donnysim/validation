<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Contracts\RuleGroup as RuleGroupContract;

class RuleGroup implements RuleGroupContract
{
    /**
     * @var array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule>
     */
    protected array $rules = [];

    /**
     * @param array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule> $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule>
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
