<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function preg_match;

class Numeric implements SingleRule
{
    public const NAME_MIXED = 'numeric.mixed';
    public const NAME_INTEGER = 'numeric.integer';
    public const NAME_FLOAT = 'numeric.float';

    public const TYPE_MIXED = 'mixed';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';

    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if ($this->type === static::TYPE_MIXED) {
            if (!is_numeric($entry->getValue())) {
                $entry->addMessageAndFinish(static::NAME_MIXED);
            }

            return;
        }

        if ($this->type === static::TYPE_INTEGER) {
            $value = $entry->getValue();

            if (
                (!is_int($value) && !is_string($value)) ||
                (is_string($value) && !preg_match('/^-?\d$/', $value))
            ) {
                $entry->addMessageAndFinish(static::NAME_INTEGER);
            }

            return;
        }

        if ($this->type === static::TYPE_FLOAT) {
            $value = $entry->getValue();

            if (
                (!is_float($value) && !is_string($value)) ||
                (is_string($value) && !preg_match('/^-?\d+\.\d+$/', $value))
            ) {
                $entry->addMessageAndFinish(static::NAME_FLOAT);
            }

            return;
        }

        $entry->addMessageAndFinish(static::NAME_MIXED);
    }
}
