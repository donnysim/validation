<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

interface RuleSet
{
    public function getPattern(): string;

    /**
     * @return array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule>
     */
    public function getRules(): array;

    public function shouldExtractData(): bool;
}
