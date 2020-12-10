<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use InvalidArgumentException;
use DonnySim\Validation\Contracts\BatchRule;
use DonnySim\Validation\Contracts\Rule;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Rules\Accepted;
use DonnySim\Validation\Rules\Alpha;
use DonnySim\Validation\Rules\AlphaDash;
use DonnySim\Validation\Rules\AlphaNum;
use DonnySim\Validation\Rules\BeforeOrAfter;
use DonnySim\Validation\Rules\Casts\ToBoolean;
use DonnySim\Validation\Rules\Confirmed;
use DonnySim\Validation\Rules\DateFormat;
use DonnySim\Validation\Rules\Email;
use DonnySim\Validation\Rules\Exists;
use DonnySim\Validation\Rules\Filled;
use DonnySim\Validation\Rules\In;
use DonnySim\Validation\Rules\IpAddress;
use DonnySim\Validation\Rules\Min;
use DonnySim\Validation\Rules\NotIn;
use DonnySim\Validation\Rules\Nullable;
use DonnySim\Validation\Rules\Numeric;
use DonnySim\Validation\Rules\OmitFromData;
use DonnySim\Validation\Rules\Required;
use DonnySim\Validation\Rules\Same;
use DonnySim\Validation\Rules\SetValueIfMissing;
use DonnySim\Validation\Rules\Sometimes;
use DonnySim\Validation\Rules\Types\ArrayType;
use DonnySim\Validation\Rules\Types\BooleanType;
use DonnySim\Validation\Rules\Types\IntegerType;
use DonnySim\Validation\Rules\Types\StringType;

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

    public function accepted(): self
    {
        $this->rules[] = new Accepted();

        return $this;
    }

    public function after($date, ?string $format = null): self
    {
        $this->rules[] = new BeforeOrAfter($date, '>', $format);

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

    public function before($date, ?string $format = null): self
    {
        $this->rules[] = new BeforeOrAfter($date, '<', $format);

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

    public function dateFormat(string $format): self
    {
        $this->rules[] = new DateFormat($format);

        return $this;
    }

    public function email(array $types = ['rfc']): self
    {
        $this->rules[] = new Email($types);

        return $this;
    }

    public function exists($table, ?string $column = null): self
    {
        $this->rules[] = new Exists($table, $column);

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

    public function notIn(array $values): self
    {
        $this->rules[] = new NotIn($values);

        return $this;
    }

    public function omitFromData(bool $value = true): self
    {
        if ($value) {
            $this->rules[] = new OmitFromData();
        }

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

    public function same(string $field): self
    {
        $this->rules[] = new Same(new FieldReference($field));

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

    public function stringType(): self
    {
        $this->rules[] = new StringType();

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
