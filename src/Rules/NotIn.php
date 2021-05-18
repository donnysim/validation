<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function in_array;

class NotIn implements SingleRule
{
    public const NAME = 'not_in';

    protected array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (in_array($entry->getValue(), $this->values, true)) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
