<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use DonnySim\Validation\Contracts\RuleSet;
use DonnySim\Validation\PathWalker;
use DonnySim\Validation\Validator;
use stdClass;
use function array_key_exists;
use function array_key_first;
use function array_merge;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function ksort;
use function mb_substr_count;
use const SORT_REGULAR;

final class ValidationProcess
{
    protected stdClass $missingValue;

    protected array $ruleSetsByNestLevel = [];

    protected array $data;

    protected array $validatedData = [];

    protected array $messages = [];

    protected Validator $validator;

    /**
     * @param \DonnySim\Validation\Contracts\RuleSet[] $ruleSets
     */
    public function __construct(Validator $validator, array $data, array $ruleSets)
    {
        $this->missingValue = new stdClass();
        $this->validator = $validator;
        $this->data = $data;
        $this->addRulesByNestLevel($ruleSets);
    }

    /**
     * @return \DonnySim\Validation\Process\ValidationMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function run(): void
    {
        $ruleSet = $this->nextRuleSet();

        while ($ruleSet !== null) {
            $this->handleRuleSet($ruleSet);

            $ruleSet = $this->nextRuleSet();
        }
    }

    /**
     * @param \DonnySim\Validation\Contracts\RuleSet[] $ruleSets
     */
    public function addRulesByNestLevel(array $ruleSets): void
    {
        foreach ($ruleSets as $ruleSet) {
            $level = mb_substr_count($ruleSet->getPattern(), '.');
            $this->ruleSetsByNestLevel[$level][] = $ruleSet;
        }

        ksort($this->ruleSetsByNestLevel, SORT_REGULAR);
    }

    public function getDataEntry(string $field): DataEntry
    {
        $value = $this->get($this->data, $field);

        if ($value === $this->missingValue) {
            return new DataEntry($field, [], $field, null, false);
        }

        return new DataEntry($field, [], $field, $value, true);
    }

    public function getOriginalDataEntry(string $field): DataEntry
    {
        $value = $this->get($this->validator->getData(), $field);

        if ($value === $this->missingValue) {
            return new DataEntry($field, [], $field, null, false);
        }

        return new DataEntry($field, [], $field, $value, true);
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    protected function handleRuleSet(RuleSet $ruleSet): void
    {
        $walker = new PathWalker($this->data);
        /** @var \DonnySim\Validation\Process\DataEntry[] $dataEntries */
        $dataEntries = [];
        $removeKeys = [];
        $mergeMessages = [];

        /** @var \DonnySim\Validation\Process\DataEntry $dataEntry */
        foreach ($walker->walk($ruleSet->getPattern()) as $dataEntry) {
            $dataPipeline = new DataEntryPipeline($this, $dataEntry, $ruleSet->getRules());
            $dataEntry->setPipeline($dataPipeline);
            $dataEntries[] = $dataEntry;
        }

        while (!empty($dataEntries)) {
            foreach ($dataEntries as $index => $entry) {
                /** @var \DonnySim\Validation\Process\DataEntryPipeline $pipeline */
                $pipeline = $entry->getPipeline();

                if ($pipeline->hasFinished()) {
                    if ($pipeline->hasFailed()) {
                        $mergeMessages[] = $entry->getMessages();
                        $removeKeys[] = $entry->getPath();
                    } elseif ($ruleSet->shouldExtractData()) {
                        $this->set($this->validatedData, $entry->getPath(), $entry->getValue());
                    }

                    unset($dataEntries[$index]);
                    continue;
                }

                if ($pipeline->isWaitingForBatchData()) {
                    /** @var \DonnySim\Validation\Contracts\BatchRule $batchRule */
                    $batchRule = $entry->getPipeline()->getCurrentRule();
                    /** @var \DonnySim\Validation\Process\DataEntry[] $batchEntries */
                    $batchEntries = [];

                    foreach ($dataEntries as $batchDataIndex => $batchDataEntry) {
                        if ($batchDataEntry->getPipeline()->getCurrentRule() === $batchRule) {
                            $batchEntries[$batchDataIndex] = $batchDataEntry;
                        }
                    }

                    $batchRule->handle($this, $batchEntries);

                    foreach ($batchEntries as $batchDataIndex => $batchDataEntry) {
                        if ($batchDataEntry->getPipeline()->hasFinished()) {
                            unset($dataEntries[$batchDataIndex]);
                        } else {
                            $batchDataEntry->getPipeline()->skip();
                        }
                    }

                    break;
                }

                $pipeline->run();
            }
        }

        $this->forget($this->data, $removeKeys);

        if ($mergeMessages) {
            $this->messages = array_merge($this->messages, ...$mergeMessages);
        }
    }

    protected function nextRuleSet(): ?RuleSet
    {
        if (empty($this->ruleSetsByNestLevel)) {
            return null;
        }

        $level = array_key_first($this->ruleSetsByNestLevel);
        $ruleSet = array_shift($this->ruleSetsByNestLevel[$level]);

        if (empty($this->ruleSetsByNestLevel[$level])) {
            unset($this->ruleSetsByNestLevel[$level]);
        }

        return $ruleSet;
    }

    protected function get($array, $key): mixed
    {
        if (!is_array($array)) {
            return $this->missingValue;
        }

        if ($key === null) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $array[$key] ?? $this->missingValue;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $this->missingValue;
            }
        }

        return $array;
    }

    protected function set(&$array, $key, $value): void
    {
        if ($key === null) {
            $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }

    protected function forget(&$array, $keys): void
    {
        $original = &$array;

        $keys = (array)$keys;

        if (empty($keys)) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (array_key_exists($key, $array)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }
}
