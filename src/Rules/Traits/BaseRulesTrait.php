<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Rules\Base\Filled;
use DonnySim\Validation\Rules\Base\Nullable;
use DonnySim\Validation\Rules\Base\OmitResult;
use DonnySim\Validation\Rules\Base\Optional;
use DonnySim\Validation\Rules\Base\Present;
use DonnySim\Validation\Rules\Base\Required;
use DonnySim\Validation\Rules\Base\SetValueIfNotPresent;

trait BaseRulesTrait
{
    public function required(): static
    {
        return $this->rule(new Required());
    }

    public function nullable(): static
    {
        return $this->rule(new Nullable());
    }

    public function filled(): static
    {
        return $this->rule(new Filled());
    }

    public function present(): static
    {
        return $this->rule(new Present());
    }

    public function optional(): static
    {
        return $this->rule(new Optional());
    }

    public function setValueIfNotPresent($value): static
    {
        return $this->rule(new SetValueIfNotPresent($value));
    }

    public function omit(): static
    {
        return $this->rule(new OmitResult());
    }
}
