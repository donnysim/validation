<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

interface RuleGroup
{
    /**
     * Get rules.
     *
     * @return \DonnySim\Validation\Contracts\Rule[]
     */
    public function getRules(): array;
}
