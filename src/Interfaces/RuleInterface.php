<?php

declare(strict_types=1);

namespace DonnySim\Validation\Interfaces;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Process\EntryProcess;

interface RuleInterface
{
    public function validate(DataEntry $entry, EntryProcess $process): void;
}
