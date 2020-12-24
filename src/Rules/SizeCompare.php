<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules\Concerns\SizeValidation;
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

    /**
     * @param string $boolean
     * @param int|float|string $value
     * @param bool $equal
     */
    public function __construct(string $boolean, $value, bool $equal)
    {
        $this->boolean = $boolean;
        $this->targetValue = $this->getValueSize($value, true);
        $this->equal = $equal;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $compareUsing = $this->getNegatedCompareFunctionName();
        $numeric = $pipeline->findPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if ($value === null || $this->targetValue === null || $value->{$compareUsing}($this->targetValue)) {
            $pipeline->fail($this->messageKey($entry->getValue(), $numeric), ['other' => $this->valueForError($this->targetValue)]);
            return;
        }
    }

    protected function getNegatedCompareFunctionName(): string
    {
        $name = null;

        switch ($this->boolean) {
            case static::BOOL_LT:
            case static::BOOL_LTE:
                $name = 'isGreaterThan';
                break;
            case static::BOOL_GT:
            case static::BOOL_GTE:
                $name = 'isLessThan';
                break;
            default:
                throw new UnexpectedValueException('Invalid comparison boolean provided.');
        }

        if ($this->equal) {
            return $name;
        }

        return "{$name}OrEqualTo";
    }

    protected function messageKey($value, bool $canBeNumeric): string
    {
        $name = null;

        switch ($this->boolean) {
            case static::BOOL_LT:
                $name = static::NAME_LT;
                break;
            case static::BOOL_LTE:
                $name = static::NAME_LTE;
                break;
            case static::BOOL_GT:
                $name = static::NAME_GT;
                break;
            case static::BOOL_GTE:
                $name = static::NAME_GTE;
                break;
            default:
                throw new UnexpectedValueException('Invalid comparison boolean provided.');
        }

        return "{$name}.{$this->getValueType($value, $canBeNumeric)}";
    }
}
