<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Base;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Process\EntryProcess;

final class OmitResult implements RuleInterface
{
    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        $process->setShouldExtractValue(false);
    }
}
