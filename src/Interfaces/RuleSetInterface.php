<?php

declare(strict_types=1);

namespace DonnySim\Validation\Interfaces;

interface RuleSetInterface
{
    public function getPattern(): string;

    public function setPattern(string $pattern);

    /**
     * @return array<int, \DonnySim\Validation\Interfaces\RuleInterface>
     */
    public function getRules(): array;

    public function rule(RuleInterface $rule);
}
