<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

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
    private ErrorSegments $errorTracker;

    /**
     * @var \DonnySim\Validation\Interfaces\RuleSetInterface[]
     */
    private array $ruleSets;

    private array $data;

    /**
     * @var array<string, \DonnySim\Validation\Interfaces\CleanupStateInterface>
     */
    private array $rulesToCleanup = [];

    private ?EntryProcess $currentEntryProcess = null;

    private Result $result;

    /**
     * @param \DonnySim\Validation\Interfaces\RuleSetInterface[] $ruleSets
     */
    public function __construct(array $data, array $ruleSets)
    {
        $this->errorTracker = new ErrorSegments();
        $this->result = new Result();
        $this->data = $data;
        $this->ruleSets = $ruleSets;
    }

    public function run(): self
    {
        $ruleSet = $this->nextRuleSet();

        while ($ruleSet !== null) {
            $this->handleRuleSet($ruleSet);

            $ruleSet = $this->nextRuleSet();
        }

        $this->cleanup();

        return $this;
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
            $this->result->addMessage($entry);
            $this->errorTracker->fail($entry->getPath());
        }
    }

    public function getResult(): Result
    {
        return $this->result;
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

    private function handleRuleSet(RuleSetInterface $ruleSet): void
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
                $this->result->set($dataEntry->getPath(), $dataEntry->getValue());
            }
        }

        $this->currentEntryProcess = null;
    }

    private function nextRuleSet(): ?RuleSetInterface
    {
        if (empty($this->ruleSets)) {
            return null;
        }

        return array_shift($this->ruleSets);
    }

    private function cleanup(): void
    {
        foreach ($this->rulesToCleanup as $rule) {
            $rule->cleanup();
        }

        $this->rulesToCleanup = [];
    }
}
