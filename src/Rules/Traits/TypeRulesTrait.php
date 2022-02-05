<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Rules\Type\ArrayType;
use DonnySim\Validation\Rules\Type\BooleanLike;
use DonnySim\Validation\Rules\Type\BooleanType;
use DonnySim\Validation\Rules\Type\IntegerType;
use DonnySim\Validation\Rules\Type\Numeric;
use DonnySim\Validation\Rules\Type\StringType;

trait TypeRulesTrait
{
    public function arrayType(): static
    {
        return $this->rule(new ArrayType());
    }

    public function booleanType(): static
    {
        return $this->rule(new BooleanType());
    }

    public function booleanLike(): static
    {
        return $this->rule(new BooleanLike());
    }

    public function integerType(): static
    {
        return $this->rule(new IntegerType());
    }

    public function numeric(): static
    {
        return $this->rule(new Numeric(Numeric::TYPE_MIXED));
    }

    public function numericFloat(): static
    {
        return $this->rule(new Numeric(Numeric::TYPE_FLOAT));
    }

    public function numericInteger(): static
    {
        return $this->rule(new Numeric(Numeric::TYPE_INTEGER));
    }

    public function stringType(): static
    {
        return $this->rule(new StringType());
    }
}
