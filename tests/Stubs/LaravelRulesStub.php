<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Stubs;

use DonnySim\Validation\Laravel\RuleSet as LaravelRuleSet;
use DonnySim\Validation\Rules;

class LaravelRulesStub extends Rules
{
    use LaravelRuleSet;
}
