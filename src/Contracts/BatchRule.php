<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

use DonnySim\Validation\Process\ValidationProcess;

interface BatchRule
{
    /**
     * @param \DonnySim\Validation\Process\ValidationProcess $process
     * @param \DonnySim\Validation\Process\DataEntry[] $entries
     */
    public function handle(ValidationProcess $process, array $entries): void;
}
