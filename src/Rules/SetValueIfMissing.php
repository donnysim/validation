<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;

class SetValueIfMissing implements SingleRule
{
    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            $entry->setValue($this->value);
            $pipeline->finish();
        }
    }
}
