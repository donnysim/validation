<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Rules\Traits\BaseRulesTrait;
use DonnySim\Validation\Rules\Traits\CastRulesTrait;
use DonnySim\Validation\Rules\Traits\IntegrityRulesTrait;
use DonnySim\Validation\Rules\Traits\RuleSetBaseTrait;
use DonnySim\Validation\Rules\Traits\TypeRulesTrait;

class RuleSet implements RuleSetInterface
{
    use RuleSetBaseTrait;
    use BaseRulesTrait;
    use CastRulesTrait;
    use IntegrityRulesTrait;
    use TypeRulesTrait;

    public static function make(string $pattern): self
    {
        return new self($pattern);
    }

    public static function ref(string $field): Reference
    {
        return new Reference($field);
    }
}
