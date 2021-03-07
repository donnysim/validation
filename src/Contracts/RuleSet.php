<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

interface RuleSet
{
    /**
     * Get attribute pattern for validation.
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Get rules.
     *
     * @return \DonnySim\Validation\Contracts\Rule[]
     */
    public function getRules(): array;
}
