<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Enums\DataProcessStateEnum;
use DonnySim\Validation\Interfaces\CleanupStateInterface;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Message;
use function array_slice;

final class EntryProcess
{
    private DataProcessStateEnum $state = DataProcessStateEnum::IDLE;

    private DataEntry $entry;

    private ValidationProcess $validationProcess;

    /**
     * @var array<int, \DonnySim\Validation\Interfaces\RuleInterface>
     */
    private array $rules;

    private int $currentRuleIndex = 0;

    private bool $failed = false;

    private bool $shouldExtractValue;

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

            if ($rule instanceof CleanupStateInterface) {
                $this->validationProcess->registerRuleCleanup($rule);
            }

            $rule->validate($this->entry, $this->validationProcess);

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
     * Replace further rules to be process with the ones provided.
     * RuleSet name is ignored.
     */
    public function fork(RuleSetInterface $ruleSet): void
    {
        $this->rules = [
            ...array_slice($this->rules, 0, $this->currentRuleIndex + 1),
            ...$ruleSet->getRules(),
        ];
    }

    /**
     * Insert rules to be executed after current rule.
     * RuleSet name is ignored.
     */
    public function insert(RuleSetInterface $ruleSet): void
    {
        $rules = $this->rules;

        $this->rules = [
            ...array_slice($rules, 0, $this->currentRuleIndex + 1),
            ...$ruleSet->getRules(),
            ...array_slice($rules, $this->currentRuleIndex + 1)
        ];
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

    private function advance(): void
    {
        if ($this->hasFailed() || !isset($this->rules[++$this->currentRuleIndex])) {
            $this->finish();
        }
    }

    private function finish(): void
    {
        $this->state = DataProcessStateEnum::FINISHED;
    }

    private function getCurrentRule(): RuleInterface|null
    {
        return $this->rules[$this->currentRuleIndex] ?? null;
    }
}
