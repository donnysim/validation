<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Contracts\BatchRule;
use DonnySim\Validation\Contracts\Rule;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Rules\Accepted;
use DonnySim\Validation\Rules\ActiveUrl;
use DonnySim\Validation\Rules\Alpha;
use DonnySim\Validation\Rules\AlphaDash;
use DonnySim\Validation\Rules\AlphaNum;
use DonnySim\Validation\Rules\Casts\ToBoolean;
use DonnySim\Validation\Rules\Confirmed;
use DonnySim\Validation\Rules\Date;
use DonnySim\Validation\Rules\DateBeforeOrAfter;
use DonnySim\Validation\Rules\DateFormat;
use DonnySim\Validation\Rules\Digits;
use DonnySim\Validation\Rules\Email;
use DonnySim\Validation\Rules\EndsWith;
use DonnySim\Validation\Rules\Filled;
use DonnySim\Validation\Rules\In;
use DonnySim\Validation\Rules\IpAddress;
use DonnySim\Validation\Rules\Json;
use DonnySim\Validation\Rules\Max;
use DonnySim\Validation\Rules\Min;
use DonnySim\Validation\Rules\NotIn;
use DonnySim\Validation\Rules\Nullable;
use DonnySim\Validation\Rules\Numeric;
use DonnySim\Validation\Rules\OmitFromData;
use DonnySim\Validation\Rules\Pipe;
use DonnySim\Validation\Rules\Present;
use DonnySim\Validation\Rules\Regex;
use DonnySim\Validation\Rules\Required;
use DonnySim\Validation\Rules\Same;
use DonnySim\Validation\Rules\SetValueIfMissing;
use DonnySim\Validation\Rules\Sometimes;
use DonnySim\Validation\Rules\StartsWith;
use DonnySim\Validation\Rules\Timezone;
use DonnySim\Validation\Rules\Types\ArrayType;
use DonnySim\Validation\Rules\Types\BooleanLike;
use DonnySim\Validation\Rules\Types\BooleanType;
use DonnySim\Validation\Rules\Types\IntegerType;
use DonnySim\Validation\Rules\Types\StringType;
use DonnySim\Validation\Rules\Url;
use DonnySim\Validation\Rules\Uuid;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class Rules
{
    /**
     * @var \DonnySim\Validation\Contracts\Rule[]
     */
    protected array $rules = [];

    protected string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public static function make(string $pattern, bool $includeInData = true): self
    {
        $instance = new static($pattern);

        if (!$includeInData) {
            $instance->omitFromData();
        }

        return $instance;
    }

    public static function reference(string $field): FieldReference
    {
        return new FieldReference($field);
    }

    public function activeUrl(): self
    {
        $this->rules[] = new ActiveUrl();

        return $this;
    }

    public function accepted(): self
    {
        $this->rules[] = new Accepted();

        return $this;
    }

    public function alpha(): self
    {
        $this->rules[] = new Alpha();

        return $this;
    }

    public function alphaDash(): self
    {
        $this->rules[] = new AlphaDash();

        return $this;
    }

    public function alphaNum(): self
    {
        $this->rules[] = new AlphaNum();

        return $this;
    }

    public function arrayType(): self
    {
        $this->rules[] = new ArrayType();

        return $this;
    }

    public function booleanLike(): self
    {
        $this->rules[] = new BooleanLike();

        return $this;
    }

    public function booleanType(): self
    {
        $this->rules[] = new BooleanType();

        return $this;
    }

    public function castToBoolean(): self
    {
        $this->rules[] = new ToBoolean();

        return $this;
    }

    public function confirmed(): self
    {
        $this->rules[] = new Confirmed();

        return $this;
    }

    public function date(): self
    {
        $this->rules[] = new Date();

        return $this;
    }

    public function dateAfter($date, ?string $format = null): self
    {
        $this->rules[] = new DateBeforeOrAfter($date, '>', $format);

        return $this;
    }

    public function dateAfterOrEqual($date, ?string $format = null): self
    {
        $this->rules[] = new DateBeforeOrAfter($date, '>=', $format);

        return $this;
    }

    public function dateBefore($date, ?string $format = null): self
    {
        $this->rules[] = new DateBeforeOrAfter($date, '<', $format);

        return $this;
    }

    public function dateBeforeOrEqual($date, ?string $format = null): self
    {
        $this->rules[] = new DateBeforeOrAfter($date, '<=', $format);

        return $this;
    }

    public function dateEqual($date, ?string $format = null): self
    {
        $this->rules[] = new DateBeforeOrAfter($date, '=', $format);

        return $this;
    }

    public function dateFormat(string $format): self
    {
        $this->rules[] = new DateFormat($format);

        return $this;
    }

    public function digits(int $digits): self
    {
        $this->rules[] = new Digits('=', $digits);

        return $this;
    }

    public function digitsBetween(int $from, int $to): self
    {
        $this->rules[] = new Digits('><', $from, $to);

        return $this;
    }

    public function email(array $types = [Email::VALIDATE_RFC]): self
    {
        $this->rules[] = new Email($types);

        return $this;
    }

    /**
     * @param string|string[] $needles
     *
     * @return static
     */
    public function endsWith($needles): self
    {
        $this->rules[] = new EndsWith(Arr::wrap($needles));

        return $this;
    }

    public function filled(): self
    {
        $this->rules[] = new Filled();

        return $this;
    }

    public function in(array $values): self
    {
        $this->rules[] = new In($values);

        return $this;
    }

    public function integerType(): self
    {
        $this->rules[] = new IntegerType();

        return $this;
    }

    public function ipAddress(?string $type = null): self
    {
        $this->rules[] = new IpAddress($type);

        return $this;
    }

    public function json(): self
    {
        $this->rules[] = new Json();

        return $this;
    }

    public function notIn(array $values): self
    {
        $this->rules[] = new NotIn($values);

        return $this;
    }

    public function notRegex(string $pattern): self
    {
        $this->rules[] = new Regex($pattern, false);

        return $this;
    }

    public function omitFromData(bool $value = true): self
    {
        if ($value) {
            $this->rules[] = new OmitFromData();
        }

        return $this;
    }

    public function present(): self
    {
        $this->rules[] = new Present();

        return $this;
    }

    public function pipe(Closure $callback): self
    {
        $this->rules[] = new Pipe($callback);

        return $this;
    }

    public function regex(string $pattern): self
    {
        $this->rules[] = new Regex($pattern, true);

        return $this;
    }

    public function required(): self
    {
        $this->rules[] = new Required();

        return $this;
    }

    public function rule(Rule $rule): self
    {
        if (!$rule instanceof SingleRule && !$rule instanceof BatchRule) {
            throw new InvalidArgumentException('Rule must implement Single or Batch rule.');
        }

        $this->rules[] = $rule;

        return $this;
    }

    public function max(int $max): self
    {
        $this->rules[] = new Max($max);

        return $this;
    }

    public function min(int $min): self
    {
        $this->rules[] = new Min($min);

        return $this;
    }

    public function nullable(): self
    {
        $this->rules[] = new Nullable();

        return $this;
    }

    public function numeric(): self
    {
        $this->rules[] = new Numeric();

        return $this;
    }

    /**
     * @param \DonnySim\Validation\FieldReference|string $field
     *
     * @return $this
     */
    public function same($field): self
    {
        $this->rules[] = new Same($this->toFieldRef($field));

        return $this;
    }

    public function setValueIfMissing($value): self
    {
        $this->rules[] = new SetValueIfMissing($value);

        return $this;
    }

    /**
     * Stop from validating if the entry is missing.
     *
     * @return static
     */
    public function sometimes(): self
    {
        $this->rules[] = new Sometimes();

        return $this;
    }

    /**
     * @param string|string[] $needles
     *
     * @return static
     */
    public function startsWith($needles): self
    {
        $this->rules[] = new StartsWith(Arr::wrap($needles));

        return $this;
    }

    public function stringType(): self
    {
        $this->rules[] = new StringType();

        return $this;
    }

    public function timezone(): self
    {
        $this->rules[] = new Timezone();

        return $this;
    }

    public function url(): self
    {
        $this->rules[] = new Url();

        return $this;
    }

    public function uuid(): self
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

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    protected function toFieldRef($value): FieldReference
    {
        if ($value instanceof FieldReference) {
            return $value;
        }

        return new FieldReference($value);
    }
}
