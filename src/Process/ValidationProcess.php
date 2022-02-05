<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use DonnySim\Validation\Data\DataWalker;
use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Message;
use function array_shift;
use function count;
use function explode;
use function is_array;

final class ValidationProcess
{
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
     * @param \DonnySim\Validation\Interfaces\RuleSetInterface[] $ruleSets
     */
    public function __construct(array $data, array $ruleSets)
    {
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
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function addMessages(Message|array $message): void
    {
        foreach (is_array($message) ? $message : [$message] as $entry) {
            $this->messages[] = $entry;
        }
    }

    /**
     * @return \DonnySim\Validation\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    protected function handleRuleSet(RuleSetInterface $ruleSet): void
    {
        /** @var \DonnySim\Validation\Data\DataEntry $dataEntry */
        foreach (DataWalker::walk($this->data, $ruleSet->getPattern()) as $dataEntry) {
            $entryProcess = new EntryProcess($this, $dataEntry, $ruleSet->getRules());
            $entryProcess->run();

            if ($entryProcess->hasFailed()) {
                continue;
            }

            if ($entryProcess->shouldExtractValue()) {
                $this->set($this->validatedData, $entryProcess->getEntry()->getPath(), $entryProcess->getEntry()->getValue());
            }
        }
    }

    protected function nextRuleSet(): ?RuleSetInterface
    {
        if (empty($this->ruleSets)) {
            return null;
        }

        return array_shift($this->ruleSets);
    }

    protected function set(array &$array, string|array $key, mixed $value): void
    {
        $keys = is_array($key) ? $key : explode('.', $key);
        $max = count($keys) - 1;

        foreach ($keys as $index => $k) {
            if ($index === $max) {
                break;
            }

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$k]) || !is_array($array[$k])) {
                $array[$k] = [];
            }

            $array = &$array[$k];
        }

        $array[$keys[$max]] = $value;
    }
}
