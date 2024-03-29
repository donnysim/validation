<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Interfaces\RuleInterface;

trait RuleSetBaseTrait
{
    protected string $pattern;

    /**
     * @var array<int, \DonnySim\Validation\Interfaces\RuleInterface>
     */
    protected array $rules = [];

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function rule(RuleInterface $rule): static
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @param \DonnySim\Validation\Interfaces\RuleInterface[] $rules
     */
    public function rules(array $rules): static
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

    public function setPattern(string $pattern): static
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
