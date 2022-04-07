<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Type;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function preg_match;

final class Numeric implements RuleInterface
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

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        if ($this->type === self::TYPE_MIXED) {
            if (!is_numeric($entry->getValue())) {
                $process->getCurrent()->fail(Message::forEntry($entry, self::NAME_MIXED));
            }

            return;
        }

        if ($this->type === self::TYPE_INTEGER) {
            $value = $entry->getValue();

            if (
                (!is_int($value) && !is_string($value))
                || (is_string($value) && !preg_match('/^-?\d$/', $value))
            ) {
                $process->getCurrent()->fail(Message::forEntry($entry, self::NAME_INTEGER));
            }

            return;
        }

        if ($this->type === self::TYPE_FLOAT) {
            $value = $entry->getValue();

            if (
                (!is_float($value) && !is_string($value))
                || (is_string($value) && !preg_match('/^-?\d+\.\d+$/', $value))
            ) {
                $process->getCurrent()->fail(Message::forEntry($entry, self::NAME_FLOAT));
            }

            return;
        }

        $process->getCurrent()->fail(Message::forEntry($entry, self::NAME_MIXED));
    }
}
