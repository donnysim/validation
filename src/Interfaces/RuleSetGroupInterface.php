<?php

declare(strict_types=1);

namespace DonnySim\Validation\Interfaces;

interface RuleSetGroupInterface
{
    /**
     * @return array<\DonnySim\Validation\RuleSet>
     */
    public function getRules(): array;
}
