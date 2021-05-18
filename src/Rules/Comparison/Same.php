<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\Reference;

class Same implements SingleRule
{
    public const NAME = 'same';

    protected Reference $reference;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $reference = $process->getDataEntry($entry->resolveSegmentWildcards($this->reference->getField()));

        if ($reference->isMissing() || $entry->getValue() !== $reference->getValue()) {
            $entry->addMessageAndFinish(static::NAME, [
                'other' => $this->reference->getField(),
            ]);
        }
    }
}
