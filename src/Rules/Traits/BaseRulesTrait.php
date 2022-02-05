<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Rules\Base\Filled;
use DonnySim\Validation\Rules\Base\Nullable;
use DonnySim\Validation\Rules\Base\OmitResult;
use DonnySim\Validation\Rules\Base\Present;
use DonnySim\Validation\Rules\Base\Required;
use DonnySim\Validation\Rules\Base\SetValueIfNotPresent;
use DonnySim\Validation\Rules\Base\Optional;

trait BaseRulesTrait
{
    public function required(): static
    {
        $this->rule(new Required());

        return $this;
    }

    public function nullable(): static
    {
        $this->rule(new Nullable());

        return $this;
    }

    public function filled(): self
    {
        $this->rules[] = new Filled();

        return $this;
    }



    public function present(): static
    {
        $this->rule(new Present());

        return $this;
    }

    public function optional(): static
    {
        $this->rule(new Optional());

        return $this;
    }

    public function setValueIfNotPresent($value): self
    {
        $this->rules[] = new SetValueIfNotPresent($value);

        return $this;
    }

    public function omit(): static
    {
        $this->rule(new OmitResult());

        return $this;
    }
}
