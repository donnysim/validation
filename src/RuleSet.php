<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Rules\Traits\BaseRulesTrait;
use DonnySim\Validation\Rules\Traits\CastRulesTrait;
use DonnySim\Validation\Rules\Traits\TypeRulesTrait;

class RuleSet implements RuleSetInterface
{
    use BaseRulesTrait;
    use CastRulesTrait;
    use TypeRulesTrait;

    protected string $pattern;

    /**
     * @var array<int, \DonnySim\Validation\Interfaces\RuleInterface>
     */
    protected array $rules = [];

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public static function make(string $pattern): self
    {
        return new self($pattern);
    }

    public static function ref(string $field): Reference
    {
        return new Reference($field);
    }

    public function rule(RuleInterface $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @param \DonnySim\Validation\Interfaces\RuleInterface[] $rules
     */
    public function rules(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->rule($rule);
        }

        return $this;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
