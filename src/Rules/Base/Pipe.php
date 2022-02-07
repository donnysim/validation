<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use Closure;
use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\EntryProcess;

final class Pipe implements RuleInterface
{
    protected Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback(...);
    }

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        ($this->callback)($entry, $process);
    }
}
