<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use DonnySim\Validation\Contracts\BatchRule;
use DonnySim\Validation\Contracts\SingleRule;

class DataEntryPipeline
{
    public const STATE_IDLE = 0;
    public const STATE_RUNNING = 1;
    public const STATE_WAITING_FOR_BATCH = 2;
    public const STATE_FINISHED = 3;

    protected int $state = self::STATE_IDLE;

    protected ValidationProcess $process;

    protected DataEntry $entry;

    /**
     * @var array<int, \DonnySim\Validation\Contracts\SingleRule|\DonnySim\Validation\Contracts\BatchRule>
     */
    protected array $rules;

    protected int $currentRuleIndex = 0;

    public function __construct(ValidationProcess $process, DataEntry $entry, array $rules)
    {
        $this->process = $process;
        $this->entry = $entry;
        $this->rules = $rules;

        if (empty($this->rules)) {
            $this->markFinished();
        }
    }

    public function run(): bool
    {
        $this->state = static::STATE_RUNNING;

        while (!$this->hasFinished()) {
            $rule = $this->getCurrentRule();

            if ($rule instanceof SingleRule) {
                $rule->handle($this->process, $this->entry);
            } elseif ($rule instanceof BatchRule) {
                $this->state = static::STATE_WAITING_FOR_BATCH;
                return false;
            }

            $this->advance();
        }

        $this->markFinished();

        return true;
    }

    public function isWaitingForBatchData(): bool
    {
        return $this->state === static::STATE_WAITING_FOR_BATCH;
    }

    public function getCurrentRule(): SingleRule|BatchRule|null
    {
        return $this->rules[$this->currentRuleIndex] ?? null;
    }

    public function hasFailed(): bool
    {
        return !empty($this->entry->getMessages());
    }

    public function hasFinished(): bool
    {
        return $this->state === static::STATE_FINISHED;
    }

    public function skip(): void
    {
        $this->advance();
    }

    public function finish(): void
    {
        $this->markFinished();
    }

    public function getPreviousRule(string $type): SingleRule|BatchRule|null
    {
        for ($i = $this->currentRuleIndex - 1; $i >= 0; $i--) {
            if ($this->rules[$i] instanceof $type) {
                return $this->rules[$i];
            }
        }

        return null;
    }

    protected function advance(): void
    {
        if ($this->hasFailed() || !isset($this->rules[++$this->currentRuleIndex])) {
            $this->markFinished();
        }
    }

    protected function markFinished(): void
    {
        $this->state = static::STATE_FINISHED;
    }
}
