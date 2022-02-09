<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use Brick\Math\BigDecimal;
use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use DonnySim\Validation\Rules\Traits\SizeValidationTrait;
use DonnySim\Validation\Rules\Type\Numeric;
use UnexpectedValueException;

final class SizeComparison implements RuleInterface
{
    use SizeValidationTrait;

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

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $compareUsing = $this->getNegatedCompareFunctionName();
        $numeric = $process->findPreviousRule(Numeric::class) !== null;
        $value = $this->getValueSize($entry->getValue(), $numeric);

        if ($value === null || $this->targetValue === null || $value->{$compareUsing}($this->targetValue)) {
            $process->fail(Message::forEntry($entry, $this->messageKey($entry->getValue(), $numeric), ['other' => $this->valueForError($this->targetValue)]));
        }
    }

    protected function getNegatedCompareFunctionName(): string
    {
        $name = match ($this->boolean) {
            self::BOOL_LT, self::BOOL_LTE => 'isGreaterThan',
            self::BOOL_GT, self::BOOL_GTE => 'isLessThan',
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
            self::BOOL_LT => self::NAME_LT,
            self::BOOL_LTE => self::NAME_LTE,
            self::BOOL_GT => self::NAME_GT,
            self::BOOL_GTE => self::NAME_GTE,
            default => throw new UnexpectedValueException('Invalid comparison boolean provided.'),
        };

        return "{$name}.{$this->getValueType($value, $canBeNumeric)}";
    }
}
