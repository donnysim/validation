<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Rules\Casts\ToBoolean;
use DonnySim\Validation\Rules\Casts\ToInteger;
use DonnySim\Validation\Rules\Casts\ToString;

trait CastRulesTrait
{
    public function toBoolean(): static
    {
        return $this->rule(new ToBoolean());
    }

    public function toInteger(): static
    {
        return $this->rule(new ToInteger());
    }

    public function toString(): static
    {
        return $this->rule(new ToString());
    }
}
