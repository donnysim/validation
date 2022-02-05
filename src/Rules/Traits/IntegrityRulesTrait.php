<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Rules\Integrity\Accepted;
use DonnySim\Validation\Rules\Integrity\Confirmed;
use DonnySim\Validation\Rules\Integrity\Distinct;
use DonnySim\Validation\Rules\Integrity\Email\Email;
use DonnySim\Validation\Rules\Integrity\In;

trait IntegrityRulesTrait
{
    public function accepted(): static
    {
        $this->rule(new Accepted());

        return $this;
    }

    public function confirmed(): static
    {
        $this->rule(new Confirmed());

        return $this;
    }

    public function distinct(): static
    {
        $this->rule(new Distinct());

        return $this;
    }

    public function email(array $types = [Email::VALIDATE_RFC, Email::VALIDATE_DNS]): static
    {
        $this->rules[] = new Email($types);

        return $this;
    }

    public function in(array $values): static
    {
        $this->rules[] = new In($values);

        return $this;
    }

    public function notIn(array $values): static
    {
        $this->rules[] = new In($values, false);

        return $this;
    }
}
