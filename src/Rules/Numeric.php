<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

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

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if ($this->type === static::TYPE_MIXED) {
            if (!\is_numeric($entry->getValue())) {
                $pipeline->fail(static::NAME_MIXED);
            }

            return;
        }


        if ($this->type === static::TYPE_INTEGER) {
            $value = $entry->getValue();

            if (
                (!\is_int($value) && !\is_string($value)) ||
                (\is_string($value) && !\preg_match("/^-?\d$/", $value))
            ) {
                $pipeline->fail(static::NAME_INTEGER);
            }

            return;
        }

        if ($this->type === static::TYPE_FLOAT) {
            $value = $entry->getValue();

            if (
                (!\is_float($value) && !\is_string($value)) ||
                (\is_string($value) && !\preg_match("/^-?\d+\.\d+$/", $value))
            ) {
                $pipeline->fail(static::NAME_FLOAT);
            }

            return;
        }

        $pipeline->fail(static::NAME_MIXED);
    }
}
