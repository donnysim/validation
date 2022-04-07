<?php

declare(strict_types=1);

namespace DonnySim\Validation\Interfaces;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;

interface RuleInterface
{
    public function validate(DataEntry $entry, ValidationProcess $process): void;
}
