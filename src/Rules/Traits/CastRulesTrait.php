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
        $this->rule(new ToBoolean());

        return $this;
    }

    public function toInteger(): static
    {
        $this->rule(new ToInteger());

        return $this;
    }

    public function toString(): static
    {
        $this->rule(new ToString());

        return $this;
    }
}
