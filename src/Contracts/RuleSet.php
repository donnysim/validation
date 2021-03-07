<?php
declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

interface RuleSet
{
    public function getPattern(): string;

    public function getRules(): array;
}
