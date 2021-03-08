<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Stubs;

use DonnySim\Validation\RuleGroup;
use DonnySim\Validation\Rules;

class ClientRuleGroupStub extends RuleGroup
{
    public function __construct()
    {
        parent::__construct([
            Rules::make('client.name')->required(),
            Rules::make('client.last_name')->required(),
        ]);
    }
}
