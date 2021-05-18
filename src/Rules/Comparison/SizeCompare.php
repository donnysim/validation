<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\Rules\Concerns\SizeValidation;
use DonnySim\Validation\Rules\Types\Numeric;
use UnexpectedValueException;

class SizeCompare implements SingleRule
{
    use SizeValidation;

    public const NAME_LT = 'less_than';
    public const NAME_LTE = 'less_than_or_equal';
    public const NAME_GT = 'greater_than';
    public const NAME_GTE = 'greater_than_or_equal';
    public const BOOL_LT = 'lt';
    public const BOOL_LTE = 'lte';
    public const BOOL_GT = 'gt';
    public const BOOL_GTE = 'gte';

    protected ?BigDecimal $targetValue;

    protected bool $equal;

    protected string $boolean;

    public function __construct(string $boolean, mixed $value, bool $equal)
    {
        $this->boolean = $boolean;
        $this->targetValue = $this->getValueSize($value, true);
        $this->equal = $equal;
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $compareUsing = $this->getNegatedCompareFunctionName();
        $numeric = $entry->getPipeline()->getPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if ($value === null || $this->targetValue === null || $value->{$compareUsing}($this->targetValue)) {
            $entry->addMessageAndFinish($this->messageKey($entry->getValue(), $numeric), ['other' => $this->valueForError($this->targetValue)]);
        }
    }

    protected function getNegatedCompareFunctionName(): string
    {
        $name = match ($this->boolean) {
            static::BOOL_LT, static::BOOL_LTE => 'isGreaterThan',
            static::BOOL_GT, static::BOOL_GTE => 'isLessThan',
            default => throw new UnexpectedValueException('Invalid comparison boolean provided.'),
        };

        if ($this->equal) {
            return $name;
        }

        return "{$name}OrEqualTo";
    }

    protected function messageKey($value, bool $canBeNumeric): string
    {
        $name = match ($this->boolean) {
            static::BOOL_LT => static::NAME_LT,
            static::BOOL_LTE => static::NAME_LTE,
            static::BOOL_GT => static::NAME_GT,
            static::BOOL_GTE => static::NAME_GTE,
            default => throw new UnexpectedValueException('Invalid comparison boolean provided.'),
        };

        return "{$name}.{$this->getValueType($value, $canBeNumeric)}";
    }
}
