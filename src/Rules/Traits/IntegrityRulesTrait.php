<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use DonnySim\Validation\Reference;
use DonnySim\Validation\Rules\Integrity\Accepted;
use DonnySim\Validation\Rules\Integrity\Alpha;
use DonnySim\Validation\Rules\Integrity\AlphaDash;
use DonnySim\Validation\Rules\Integrity\AlphaNum;
use DonnySim\Validation\Rules\Integrity\Between;
use DonnySim\Validation\Rules\Integrity\Confirmed;
use DonnySim\Validation\Rules\Integrity\Date\Date;
use DonnySim\Validation\Rules\Integrity\Date\DateComparison;
use DonnySim\Validation\Rules\Integrity\Date\DateFormat;
use DonnySim\Validation\Rules\Integrity\Different;
use DonnySim\Validation\Rules\Integrity\Digits;
use DonnySim\Validation\Rules\Integrity\Distinct;
use DonnySim\Validation\Rules\Integrity\Email\Email;
use DonnySim\Validation\Rules\Integrity\EndsWith;
use DonnySim\Validation\Rules\Integrity\In;
use DonnySim\Validation\Rules\Integrity\IpAddress;
use DonnySim\Validation\Rules\Integrity\SizeComparison;
use DonnySim\Validation\Rules\Integrity\StartsWith;
use function is_array;

trait IntegrityRulesTrait
{
    public function accepted(): static
    {
        return $this->rule(new Accepted());
    }

    public function alpha(): static
    {
        $this->rule(new Alpha());

        return $this;
    }

    public function alphaDash(): static
    {
        $this->rule(new AlphaDash());

        return $this;
    }

    public function alphaNum(): static
    {
        $this->rule(new AlphaNum());

        return $this;
    }

    /**
     * @param float|int|string $min Non string floats will be compared using 14 precision.
     * @param float|int|string $max Non string floats will be compared using 14 precision.
     */
    public function between(float|int|string $min, float|int|string $max): static
    {
        return $this->rule(new Between($min, $max));
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

    public function different(Reference|string $reference): static
    {
        return $this->rule(new Different(Reference::make($reference)));
    }

    public function digits(int $digits): static
    {
        return $this->rule(new Digits('=', $digits));
    }

    public function digitsBetween(int $from, int $to): static
    {
        return $this->rule(new Digits('><', $from, $to));
    }

    public function in(array $values): static
    {
        return $this->rule(new In($values));
    }

    public function ip(?string $type = null): static
    {
        return $this->rule(new IpAddress($type));
    }

    public function ipv4(): static
    {
        return $this->rule(new IpAddress(IpAddress::TYPE_IPV4));
    }

    public function ipv6(): static
    {
        return $this->rule(new IpAddress(IpAddress::TYPE_IPV6));
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
        return $this->rule(new EndsWith(is_array($needles) ? $needles : [$needles]));
    }

    /**
     * @param string|array<string> $needles
     */
    public function startsWith(array|string $needles): static
    {
        return $this->rule(new StartsWith(is_array($needles) ? $needles : [$needles]));
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     */
    public function lessThan(mixed $value, bool $allowEqual = false): static
    {
        return $this->rule(new SizeComparison(SizeComparison::BOOL_LT, $value, $allowEqual));
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     */
    public function lessThanOrEqual(mixed $value): static
    {
        return $this->lessThan($value, true);
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     */
    public function greaterThan(mixed $value, bool $allowEqual = false): static
    {
        return $this->rule(new SizeComparison(SizeComparison::BOOL_GT, $value, $allowEqual));
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     */
    public function greaterThanOrEqual(mixed $value): static
    {
        return $this->greaterThan($value, true);
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     */
    public function max(mixed $value): static
    {
        return $this->lessThanOrEqual($value);
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     */
    public function min(mixed $value): static
    {
        return $this->greaterThanOrEqual($value);
    }
}
