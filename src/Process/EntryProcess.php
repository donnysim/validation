<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Enums\DataProcessStateEnum;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;

final class EntryProcess
{
    protected DataProcessStateEnum $state = DataProcessStateEnum::IDLE;

    protected DataEntry $entry;

    protected ValidationProcess $validationProcess;

    /**
     * @var array<int, \DonnySim\Validation\Interfaces\RuleInterface>
     */
    protected array $rules;

    protected int $currentRuleIndex = 0;

    protected bool $failed = false;

    protected bool $shouldExtractValue;

    public function __construct(ValidationProcess $validationProcess, DataEntry $entry, array $rules)
    {
        $this->validationProcess = $validationProcess;
        $this->entry = $entry;
        $this->rules = $rules;
        $this->shouldExtractValue = $entry->isPresent();
    }

    public function getEntry(): DataEntry
    {
        return $this->entry;
    }

    public function setShouldExtractValue(bool $shouldExtractValue): void
    {
        $this->shouldExtractValue = $shouldExtractValue;
    }

    public function shouldExtractValue(): bool
    {
        return $this->shouldExtractValue;
    }

    public function run(): void
    {
        $this->state = DataProcessStateEnum::RUNNING;

        while (!$this->hasFinished()) {
            $rule = $this->getCurrentRule();

            if (!$rule) {
                break;
            }

            $rule->validate($this->entry, $this);

            if ($this->hasFailed() || $this->isStopped()) {
                break;
            }

            $this->advance();
        }

        $this->finish();
    }

    /**
     * @param \DonnySim\Validation\Message|\DonnySim\Validation\Message[] $message
     */
    public function fail(Message|array $message): void
    {
        $this->shouldExtractValue = false;
        $this->failed = true;

        $this->validationProcess->addMessages($message);
    }

    public function hasFinished(): bool
    {
        return $this->state === DataProcessStateEnum::FINISHED || $this->hasFailed();
    }

    public function hasFailed(): bool
    {
        return $this->failed;
    }

    public function isStopped(): bool
    {
        return $this->state === DataProcessStateEnum::STOPPED;
    }

    public function stop(): void
    {
        $this->state = DataProcessStateEnum::STOPPED;
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function findPreviousRule(string $class): ?RuleInterface
    {
        for ($i = $this->currentRuleIndex; $i >= 0; $i--) {
            if ($this->rules[0] instanceof $class) {
                return $this->rules[0];
            }
        }

        return null;
    }

    protected function advance(): void
    {
        if ($this->hasFailed() || !isset($this->rules[++$this->currentRuleIndex])) {
            $this->finish();
        }
    }

    protected function finish(): void
    {
        $this->state = DataProcessStateEnum::FINISHED;
    }

    protected function getCurrentRule(): RuleInterface|null
    {
        return $this->rules[$this->currentRuleIndex] ?? null;
    }
}
