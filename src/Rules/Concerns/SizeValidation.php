<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Concerns;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

trait SizeValidation
{
    public function getValueSize($value, bool $canBeNumeric): ?BigDecimal
    {
        if ($value === null) {
            return null;
        }

        if (\is_int($value)) {
            return BigDecimal::of($value);
        }

        if (\is_float($value)) {
            return BigDecimal::of($value)->toScale(14, RoundingMode::HALF_EVEN);
        }

        if ($canBeNumeric && \is_numeric($value)) {
            return BigDecimal::of($value);
        }

        if (\is_string($value)) {
            return BigDecimal::of(\mb_strlen($value));
        }

        if (\is_array($value)) {
            return BigDecimal::of(\count($value));
        }

        return null;
    }

    public function getValueType($value, bool $canBeNumeric): string
    {
        if ($value === null) {
            return 'string';
        }

        if (\is_int($value) || \is_float($value) || ($canBeNumeric && \is_numeric($value))) {
            return 'numeric';
        }

        if (\is_string($value)) {
            return 'string';
        }

        if (\is_array($value)) {
            return 'array';
        }

        return 'string';
    }

    public function valueForError(BigDecimal $value): string
    {
        $value = (string)$value;

        if (\preg_match("/^-?\d+\.\d+$/", $value)) {
            return \rtrim($value, '.0');
        }

        return $value;
    }
}
