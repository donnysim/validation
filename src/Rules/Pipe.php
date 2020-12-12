<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use Closure;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Pipe implements SingleRule
{
    protected Closure $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        ($this->callback)($pipeline, $entry);
    }
}
