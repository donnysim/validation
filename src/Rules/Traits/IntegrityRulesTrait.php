<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Rules\Integrity\Accepted;
use DonnySim\Validation\Rules\Integrity\Confirmed;
use DonnySim\Validation\Rules\Integrity\Date\Date;
use DonnySim\Validation\Rules\Integrity\Date\DateComparison;
use DonnySim\Validation\Rules\Integrity\Date\DateFormat;
use DonnySim\Validation\Rules\Integrity\Distinct;
use DonnySim\Validation\Rules\Integrity\Email\Email;
use DonnySim\Validation\Rules\Integrity\EndsWith;
use DonnySim\Validation\Rules\Integrity\In;
use DonnySim\Validation\Rules\Integrity\StartsWith;
use function is_array;

trait IntegrityRulesTrait
{
    public function accepted(): static
    {
        return $this->rule(new Accepted());
    }

    public function confirmed(): static
    {
        return $this->rule(new Confirmed());
    }

    public function distinct(): static
    {
        return $this->rule(new Distinct());
    }

    public function email(array $types = [Email::VALIDATE_RFC, Email::VALIDATE_DNS]): static
    {
        return $this->rule(new Email($types));
    }

    public function date(): static
    {
        return $this->rule(new Date());
    }

    public function dateAfter($date, ?string $format = null): static
    {
        return $this->rule(new DateComparison($date, '>', $format));
    }

    public function dateAfterOrEqual($date, ?string $format = null): static
    {
        return $this->rule(new DateComparison($date, '>=', $format));
    }

    public function dateBefore($date, ?string $format = null): static
    {
        return $this->rule(new DateComparison($date, '<', $format));
    }

    public function dateBeforeOrEqual($date, ?string $format = null): static
    {
        return $this->rule(new DateComparison($date, '<=', $format));
    }

    public function dateEqual($date, ?string $format = null): static
    {
        return $this->rule(new DateComparison($date, '=', $format));
    }

    public function dateFormat(string $format): static
    {
        return $this->rule(new DateFormat($format));
    }

    public function in(array $values): static
    {
        return $this->rule(new In($values));
    }

    public function notIn(array $values): static
    {
        return $this->rule(new In($values, false));
    }

    /**
     * @param string|array<string> $needles
     */
    public function endsWith(array|string $needles): static
    {
        $this->rules[] = new EndsWith(is_array($needles) ? $needles : [$needles]);

        return $this;
    }

    /**
     * @param string|array<string> $needles
     */
    public function startsWith(array|string $needles): static
    {
        $this->rules[] = new StartsWith(is_array($needles) ? $needles : [$needles]);

        return $this;
    }
}
