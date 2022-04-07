<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use DonnySim\Validation\Data\Arr;
use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Data\DataWalker;
use DonnySim\Validation\ErrorSegments;
use DonnySim\Validation\Interfaces\CleanupStateInterface;
use DonnySim\Validation\Interfaces\RuleSetGroupInterface;
use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Message;
use function array_merge;
use function array_shift;
use function is_array;
use function spl_object_id;

final class ValidationProcess
{
    protected ErrorSegments $errorTracker;

    /**
     * @var \DonnySim\Validation\Interfaces\RuleSetInterface[]
     */
    protected array $ruleSets;

    protected array $data;

    protected array $validatedData = [];

    /**
     * @var \DonnySim\Validation\Message[]
     */
    protected array $messages = [];

    /**
     * @var array<string, \DonnySim\Validation\Interfaces\CleanupStateInterface>
     */
    protected array $rulesToCleanup = [];

    protected ?EntryProcess $currentEntryProcess = null;

    /**
     * @param \DonnySim\Validation\Interfaces\RuleSetInterface[] $ruleSets
     */
    public function __construct(array $data, array $ruleSets)
    {
        $this->errorTracker = new ErrorSegments();
        $this->data = $data;
        $this->ruleSets = $ruleSets;
    }

    public function run(): void
    {
        $ruleSet = $this->nextRuleSet();

        while ($ruleSet !== null) {
            $this->handleRuleSet($ruleSet);

            $ruleSet = $this->nextRuleSet();
        }

        $this->cleanup();
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function getEntry(string $path): DataEntry
    {
        foreach (DataWalker::walk($this->data, $path) as $entry) {
            return $entry;
        }

        return new DataEntry($path, [], $path, null, false);
    }

    /**
     * @return \DonnySim\Validation\Data\DataEntry[]
     */
    public function getAllEntries(string $pattern): array
    {
        $entries = [];

        foreach (DataWalker::walk($this->data, $pattern) as $entry) {
            $entries[] = $entry;
        }

        return $entries;
    }

    public function addMessages(Message|array $message): void
    {
        foreach (is_array($message) ? $message : [$message] as $entry) {
            $this->messages[] = $entry;

            $this->errorTracker->fail($entry->getPath());
        }
    }

    /**
     * @return \DonnySim\Validation\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getCurrent(): EntryProcess
    {
        return $this->currentEntryProcess;
    }

    public function addRuleSet(RuleSetInterface|RuleSetGroupInterface $set): void
    {
        if ($set instanceof RuleSetInterface) {
            $this->ruleSets[] = $set;
        } else {
            $this->ruleSets = array_merge($this->ruleSets, $set->getRules());
        }
    }

    public function registerRuleCleanup(CleanupStateInterface $rule): void
    {
        $this->rulesToCleanup[spl_object_id($rule)] = $rule;
    }

    protected function handleRuleSet(RuleSetInterface $ruleSet): void
    {
        if ($this->errorTracker->hasFailed($ruleSet->getPattern())) {
            return;
        }

        /** @var \DonnySim\Validation\Data\DataEntry $dataEntry */
        foreach (DataWalker::walk($this->data, $ruleSet->getPattern()) as $dataEntry) {
            if ($this->errorTracker->hasFailed($dataEntry->getPath())) {
                continue;
            }

            $this->currentEntryProcess = new EntryProcess($this, $dataEntry, $ruleSet->getRules());
            $this->currentEntryProcess->run();

            if ($this->currentEntryProcess->hasFailed()) {
                continue;
            }

            if ($this->currentEntryProcess->shouldExtractValue()) {
                Arr::set($this->validatedData, $dataEntry->getPath(), $dataEntry->getValue());
            }
        }

        $this->currentEntryProcess = null;
    }

    protected function nextRuleSet(): ?RuleSetInterface
    {
        if (empty($this->ruleSets)) {
            return null;
        }

        return array_shift($this->ruleSets);
    }

    protected function cleanup(): void
    {
        foreach ($this->rulesToCleanup as $rule) {
            $rule->cleanup();
        }

        $this->rulesToCleanup = [];
    }
}
