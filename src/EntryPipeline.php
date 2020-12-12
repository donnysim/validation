<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Contracts\BatchRule;
use DonnySim\Validation\Contracts\Rule;
use DonnySim\Validation\Contracts\SingleRule;

class EntryPipeline
{
    public const STATE_IDLE = 0;
    public const STATE_RUNNING = 1;
    public const STATE_WAITING_FOR_BATCH = 2;
    public const STATE_FINISHED = 3;

    protected int $state = self::STATE_IDLE;

    protected Validator $validator;

    protected Entry $entry;

    /**
     * @var \DonnySim\Validation\Contracts\Rule[]
     */
    protected array $rules;

    protected int $currentRuleIndex = 0;

    /**
     * @var \DonnySim\Validation\Message[]
     */
    protected array $messages = [];

    protected bool $includeInData = true;

    public function __construct(Validator $validator, Entry $entry, array $rules)
    {
        $this->validator = $validator;
        $this->entry = $entry;
        $this->rules = $rules;

        if (empty($this->rules)) {
            $this->markFinished();
        }
    }

    public function getValidator(): Validator
    {
        return $this->validator;
    }

    public function getCurrentRule(): ?Rule
    {
        return $this->rules[$this->currentRuleIndex] ?? null;
    }

    public function omitFromData(): void
    {
        $this->includeInData = false;
    }

    public function shouldExtractData(): bool
    {
        return $this->includeInData;
    }

    public function run(): bool
    {
        $this->state = static::STATE_RUNNING;

        while (!$this->hasFinished()) {
            $rule = $this->getCurrentRule();

            if ($rule instanceof SingleRule) {
                $rule->handle($this, $this->entry);
            } elseif ($rule instanceof BatchRule) {
                $this->state = static::STATE_WAITING_FOR_BATCH;
                return false;
            }

            $this->advance();
        }

        $this->markFinished();

        return true;
    }

    /**
     * Append rules after current one to be processed next.
     *
     * @param \Closure $callback
     */
    public function insertNext(Closure $callback): void
    {
        $rules = Rules::make($this->getEntry()->getPath());
        $callback($rules);
        $this->insertRulesAfter($rules);
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    /**
     * @return \DonnySim\Validation\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function fail(string $key, array $params = []): void
    {
        $this->messages[] = new Message($this->entry, $key, $params);

        $this->markFinished();
    }

    public function findPreviousRule(string $type): ?Rule
    {
        $index = $this->currentRuleIndex;

        while ($index >= 0) {
            if ($this->rules[$index] instanceof $type) {
                return $this->rules[$index];
            }

            $index--;
        }

        return null;
    }

    public function isWaitingForBatchRule(): bool
    {
        return $this->state === static::STATE_WAITING_FOR_BATCH;
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

    protected function markFinished(): void
    {
        $this->state = static::STATE_FINISHED;
    }

    protected function advance(): void
    {
        if (!isset($this->rules[$this->currentRuleIndex + 1])) {
            $this->markFinished();
            return;
        }

        $this->currentRuleIndex++;
    }

    protected function insertRulesAfter(Rules $rules)
    {
        $this->rules = \array_merge(\array_slice($this->rules, 0, $this->currentRuleIndex + 1), $rules->getRules(), \array_slice($this->rules, $this->currentRuleIndex + 1));
    }
}
