<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Contracts\RuleSet;

class Rules implements RuleSet
{
    use DefaultRules;

    /**
     * @var array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule>
     */
    protected array $rules = [];

    protected string $pattern;

    protected bool $extractData = true;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public static function make(string $pattern): static
    {
        return new static($pattern);
    }

    public static function reference(string $pattern): Reference
    {
        return new Reference($pattern);
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function shouldExtractData(): bool
    {
        return $this->extractData;
    }

    public function omitFromData(bool $value = false): self
    {
        $this->extractData = $value;

        return $this;
    }
}
