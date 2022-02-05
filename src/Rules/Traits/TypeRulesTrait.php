<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Rules\Type\ArrayType;
use DonnySim\Validation\Rules\Type\BooleanLike;
use DonnySim\Validation\Rules\Type\BooleanType;
use DonnySim\Validation\Rules\Type\IntegerType;
use DonnySim\Validation\Rules\Type\StringType;

trait TypeRulesTrait
{
    public function arrayType(): static
    {
        $this->rule(new ArrayType());

        return $this;
    }

    public function booleanType(): static
    {
        $this->rule(new BooleanType());

        return $this;
    }

    public function booleanLike(): static
    {
        $this->rule(new BooleanLike());

        return $this;
    }

    public function integerType(): static
    {
        $this->rule(new IntegerType());

        return $this;
    }

    public function stringType(): static
    {
        $this->rule(new StringType());

        return $this;
    }
}
