<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;

interface SingleRule
{
    public function handle(ValidationProcess $process, DataEntry $entry): void;
}
