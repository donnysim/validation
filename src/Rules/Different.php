<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use DonnySim\Validation\FieldReference;

class Different implements SingleRule
{
    public const NAME = 'different';

    protected FieldReference $reference;

    public function __construct(FieldReference $reference)
    {
        $this->reference = $reference;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $reference = $pipeline->getValidator()->getValueEntry($entry->resolvePathWildcards($this->reference->getField()));

        if ($reference->isMissing()) {
            return;
        }

        if ($entry->getValue() === $reference->getValue()) {
            $pipeline->fail(static::NAME, [
                'other' => $this->reference->getField(),
            ]);
        }
    }
}
