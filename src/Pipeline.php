<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Generator;
use DonnySim\Validation\Contracts\Rule;

class Pipeline
{
    /**
     * @var \DonnySim\Validation\EntryPipeline[]
     */
    protected array $entryPipelines = [];

    public function run(): void
    {
        while (!$this->allPipelinesFinished()) {
            foreach ($this->unfinishedPipelines() as $entryPipeline) {
                if ($entryPipeline->isWaitingForBatchRule()) {
                    /** @var \DonnySim\Validation\Contracts\BatchRule $rule */
                    $rule = $entryPipeline->getCurrentRule();
                    $pipelines = $this->getPipelinesForBatch($rule);

                    $rule->handle($pipelines);

                    foreach ($pipelines as $pipeline) {
                        $pipeline->skip();
                    }

                    break;
                }

                $entryPipeline->run();
            }
        }
    }

    public function add(EntryPipeline $pipeline): void
    {
        $this->entryPipelines[] = $pipeline;
    }

    /**
     * @return \DonnySim\Validation\EntryPipeline[]
     */
    public function getEntryPipelines(): array
    {
        return $this->entryPipelines;
    }

    /**
     * @return \Generator|\DonnySim\Validation\EntryPipeline[]
     */
    protected function unfinishedPipelines(): Generator
    {
        foreach ($this->entryPipelines as $entryPipeline) {
            if (!$entryPipeline->hasFinished()) {
                yield $entryPipeline;
            }
        }
    }

    /**
     * @param \DonnySim\Validation\Contracts\Rule $rule
     *
     * @return \DonnySim\Validation\EntryPipeline[]
     */
    protected function getPipelinesForBatch(Rule $rule): array
    {
        $pipelines = [];

        foreach ($this->entryPipelines as $pipeline) {
            if (!$pipeline->isWaitingForBatchRule() || $pipeline->hasFinished()) {
                continue;
            }

            if ($pipeline->getCurrentRule() === $rule) {
                $pipelines[] = $pipeline;
            }
        }

        return $pipelines;
    }

    protected function allPipelinesFinished(): bool
    {
        foreach ($this->entryPipelines as $entryPipeline) {
            if (!$entryPipeline->hasFinished()) {
                return false;
            }
        }

        return true;
    }
}
