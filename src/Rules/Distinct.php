<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\BatchRule;
use function array_search;
use function array_splice;

class Distinct implements BatchRule
{
    public const NAME = 'distinct';

    /**
     * @param \DonnySim\Validation\Data\EntryPipeline[] $pipelines
     */
    public function handle(array $pipelines): void
    {
        $processedValues = [];
        /** @var \DonnySim\Validation\Data\EntryPipeline[] $processedPipelines */
        $processedPipelines = [];

        foreach ($pipelines as $pipeline) {
            if ($pipeline->getEntry()->isMissing()) {
                continue;
            }

            $index = array_search($pipeline->getEntry()->getValue(), $processedValues, true);
            if ($index !== false && $index >= 0) {
                $processedPipelines[$index]->fail(static::NAME);
                $pipeline->fail(static::NAME);

                array_splice($processedValues, $index, 1);
                array_splice($processedPipelines, $index, 1);
                continue;
            }

            $processedValues[] = $pipeline->getEntry()->getValue();
            $processedPipelines[] = $pipeline;
        }
    }
}
