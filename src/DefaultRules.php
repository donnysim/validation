<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Contracts\BatchRule;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Rules\Accepted;
use DonnySim\Validation\Rules\Casts\ToBoolean;
use DonnySim\Validation\Rules\Comparison\Between;
use DonnySim\Validation\Rules\Comparison\DateBeforeOrAfter;
use DonnySim\Validation\Rules\Comparison\DateFormat;
use DonnySim\Validation\Rules\Comparison\Different;
use DonnySim\Validation\Rules\Comparison\Digits;
use DonnySim\Validation\Rules\Comparison\Distinct;
use DonnySim\Validation\Rules\Comparison\EndsWith;
use DonnySim\Validation\Rules\Comparison\Regex;
use DonnySim\Validation\Rules\Comparison\Same;
use DonnySim\Validation\Rules\Comparison\SizeCompare;
use DonnySim\Validation\Rules\Comparison\StartsWith;
use DonnySim\Validation\Rules\Confirmed;
use DonnySim\Validation\Rules\Filled;
use DonnySim\Validation\Rules\In;
use DonnySim\Validation\Rules\NotIn;
use DonnySim\Validation\Rules\Nullable;
use DonnySim\Validation\Rules\Present;
use DonnySim\Validation\Rules\Required;
use DonnySim\Validation\Rules\SetValueIfMissing;
use DonnySim\Validation\Rules\Sometimes;
use DonnySim\Validation\Rules\Types\ActiveUrl;
use DonnySim\Validation\Rules\Types\Alpha;
use DonnySim\Validation\Rules\Types\AlphaDash;
use DonnySim\Validation\Rules\Types\AlphaNum;
use DonnySim\Validation\Rules\Types\ArrayType;
use DonnySim\Validation\Rules\Types\BooleanLike;
use DonnySim\Validation\Rules\Types\BooleanType;
use DonnySim\Validation\Rules\Types\Date;
use DonnySim\Validation\Rules\Types\Email;
use DonnySim\Validation\Rules\Types\IntegerType;
use DonnySim\Validation\Rules\Types\IpAddress;
use DonnySim\Validation\Rules\Types\Json;
use DonnySim\Validation\Rules\Types\Numeric;
use DonnySim\Validation\Rules\Types\StringType;
use DonnySim\Validation\Rules\Types\Timezone;
use DonnySim\Validation\Rules\Types\Url;
use DonnySim\Validation\Rules\Types\Uuid;
use function is_array;

trait DefaultRules
{
    public function accepted(): static
    {
        $this->rules[] = new Accepted();

        return $this;
    }

    public function activeUrl(): self
    {
        $this->rules[] = new ActiveUrl();

        return $this;
    }

    public function alpha(): static
    {
        $this->rules[] = new Alpha();

        return $this;
    }

    public function alphaDash(): static
    {
        $this->rules[] = new AlphaDash();

        return $this;
    }

    public function alphaNum(): static
    {
        $this->rules[] = new AlphaNum();

        return $this;
    }

    public function arrayType(): static
    {
        $this->rules[] = new ArrayType();

        return $this;
    }

    /**
     * @param mixed $min Non string floats will be compared using 14 precision.
     * @param mixed $max Non string floats will be compared using 14 precision.
     *
     * @return static
     */
    public function between(mixed $min, mixed $max): static
    {
        $this->rules[] = new Between($min, $max);

        return $this;
    }

    public function booleanLike(): static
    {
        $this->rules[] = new BooleanLike();

        return $this;
    }

    public function booleanType(): static
    {
        $this->rules[] = new BooleanType();

        return $this;
    }

    public function castToBoolean(): static
    {
        $this->rules[] = new ToBoolean();

        return $this;
    }

    public function confirmed(): static
    {
        $this->rules[] = new Confirmed();

        return $this;
    }

    public function date(): static
    {
        $this->rules[] = new Date();

        return $this;
    }

    public function dateAfter($date, ?string $format = null): static
    {
        $this->rules[] = new DateBeforeOrAfter($date, '>', $format);

        return $this;
    }

    public function dateAfterOrEqual($date, ?string $format = null): static
    {
        $this->rules[] = new DateBeforeOrAfter($date, '>=', $format);

        return $this;
    }

    public function dateBefore($date, ?string $format = null): static
    {
        $this->rules[] = new DateBeforeOrAfter($date, '<', $format);

        return $this;
    }

    public function dateBeforeOrEqual($date, ?string $format = null): static
    {
        $this->rules[] = new DateBeforeOrAfter($date, '<=', $format);

        return $this;
    }

    public function dateEqual($date, ?string $format = null): static
    {
        $this->rules[] = new DateBeforeOrAfter($date, '=', $format);

        return $this;
    }

    public function dateFormat(string $format): static
    {
        $this->rules[] = new DateFormat($format);

        return $this;
    }

    public function different(Reference|string $field): static
    {
        $this->rules[] = new Different($this->toFieldRef($field));

        return $this;
    }

    public function digits(int $digits): static
    {
        $this->rules[] = new Digits('=', $digits);

        return $this;
    }

    public function digitsBetween(int $from, int $to): static
    {
        $this->rules[] = new Digits('><', $from, $to);

        return $this;
    }

    public function distinct(): static
    {
        $this->rules[] = new Distinct();

        return $this;
    }

    public function email(array $types = [Email::VALIDATE_RFC]): static
    {
        $this->rules[] = new Email($types);

        return $this;
    }

    /**
     * @param string|string[] $needles
     *
     * @return static
     */
    public function endsWith(array|string $needles): static
    {
        $this->rules[] = new EndsWith(is_array($needles) ? $needles : [$needles]);

        return $this;
    }

    public function filled(): static
    {
        $this->rules[] = new Filled();

        return $this;
    }

    public function integerType(): static
    {
        $this->rules[] = new IntegerType();

        return $this;
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     * @param bool $allowEqual
     *
     * @return static
     */
    public function lessThan(mixed $value, bool $allowEqual = false): static
    {
        $this->rules[] = new SizeCompare(SizeCompare::BOOL_LT, $value, $allowEqual);

        return $this;
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     *
     * @return static
     */
    public function lessThanOrEqual(mixed $value): static
    {
        return $this->lessThan($value, true);
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     * @param bool $allowEqual
     *
     * @return static
     */
    public function greaterThan(mixed $value, bool $allowEqual = false): static
    {
        $this->rules[] = new SizeCompare(SizeCompare::BOOL_GT, $value, $allowEqual);

        return $this;
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     *
     * @return static
     */
    public function greaterThanOrEqual(mixed $value): static
    {
        return $this->greaterThan($value, true);
    }

    public function in(array $values): static
    {
        $this->rules[] = new In($values);

        return $this;
    }

    public function ipAddress(?string $type = null): static
    {
        $this->rules[] = new IpAddress($type);

        return $this;
    }

    public function json(): static
    {
        $this->rules[] = new Json();

        return $this;
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     *
     * @return static
     */
    public function max(mixed $value): static
    {
        return $this->lessThanOrEqual($value);
    }

    /**
     * @param mixed $value Non string floats will be compared using 14 precision.
     *
     * @return static
     */
    public function min(mixed $value): static
    {
        return $this->greaterThanOrEqual($value);
    }

    public function notIn(array $values): static
    {
        $this->rules[] = new NotIn($values);

        return $this;
    }

    public function nullable(): static
    {
        $this->rules[] = new Nullable();

        return $this;
    }

    public function numeric(): static
    {
        $this->rules[] = new Numeric(Numeric::TYPE_MIXED);

        return $this;
    }

    public function numericFloat(): static
    {
        $this->rules[] = new Numeric(Numeric::TYPE_FLOAT);

        return $this;
    }

    public function numericInteger(): static
    {
        $this->rules[] = new Numeric(Numeric::TYPE_INTEGER);

        return $this;
    }

    public function notRegex(string $pattern): static
    {
        $this->rules[] = new Regex($pattern, false);

        return $this;
    }

    public function present(): static
    {
        $this->rules[] = new Present();

        return $this;
    }

    public function regex(string $pattern): static
    {
        $this->rules[] = new Regex($pattern, true);

        return $this;
    }

    public function required(): static
    {
        $this->rules[] = new Required();

        return $this;
    }

    public function rule(SingleRule|BatchRule $rule): static
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @param array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule> $rules
     *
     * @return static
     */
    public function rules(array $rules): static
    {
        foreach ($rules as $rule) {
            $this->rule($rule);
        }

        return $this;
    }

    public function same(Reference|string $field): static
    {
        $this->rules[] = new Same($this->toFieldRef($field));

        return $this;
    }

    public function setValueIfMissing($value): static
    {
        $this->rules[] = new SetValueIfMissing($value);

        return $this;
    }

    /**
     * Stop from validating if the entry is missing.
     *
     * @return static
     */
    public function sometimes(): static
    {
        $this->rules[] = new Sometimes();

        return $this;
    }

    /**
     * @param string|string[] $needles
     *
     * @return static
     */
    public function startsWith(array|string $needles): static
    {
        $this->rules[] = new StartsWith(is_array($needles) ? $needles : [$needles]);

        return $this;
    }

    public function stringType(): static
    {
        $this->rules[] = new StringType();

        return $this;
    }

    public function timezone(): static
    {
        $this->rules[] = new Timezone();

        return $this;
    }

    public function url(): self
    {
        $this->rules[] = new Url();

        return $this;
    }

    public function uuid(): static
    {
        $this->rules[] = new Uuid();

        return $this;
    }

    public function when(bool $condition, Closure $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    protected function toFieldRef($value): Reference
    {
        if ($value instanceof Reference) {
            return $value;
        }

        return new Reference($value);
    }
}
