<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\Reference;

final class Different implements RuleInterface
{
    public const NAME = 'different';

    protected Reference $reference;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }

    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $reference = $process->getEntry($entry->resolveSegmentWildcards($this->reference->getField()));

        if ($reference->isNotPresent()) {
            return;
        }

        if ($entry->getValue() === $reference->getValue()) {
            $process->getCurrent()->fail(Message::forEntry($entry, self::NAME, ['other' => $this->reference->getField()]));
        }
    }
}
