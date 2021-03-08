<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Contracts\RuleGroup as RuleGroupContract;

class RuleGroup implements RuleGroupContract
{
    /**
     * @var array|\DonnySim\Validation\Contracts\RuleSet[]
     */
    protected array $rules = [];

    /**
     * @param \DonnySim\Validation\Contracts\RuleSet[] $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
