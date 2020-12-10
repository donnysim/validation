<?php

declare(strict_types=1);

namespace DonnySim\Validation\Contracts;

interface BatchRule extends Rule
{
    /**
     * @param \DonnySim\Validation\EntryPipeline[] $pipelines
     */
    public function handle(array $pipelines): void;
}
