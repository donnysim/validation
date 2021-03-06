<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Contracts\MessageOverrideProvider;
use DonnySim\Validation\Contracts\MessageResolver;
use DonnySim\Validation\Contracts\RuleGroup as RuleGroupContract;
use DonnySim\Validation\Contracts\RuleSet;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use DonnySim\Validation\Data\EntryPipelineCollection;
use DonnySim\Validation\Exceptions\InvalidRuleException;
use DonnySim\Validation\Exceptions\ValidationException;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use function is_array;

class Validator implements MessageOverrideProvider
{
    protected static ?Closure $failureHandler = null;

    protected MessageResolver $resolver;

    protected string $missingValue;

    protected array $data;

    /**
     * @var \DonnySim\Validation\Contracts\RuleSet[]
     */
    protected array $rules = [];

    protected array $validatedData = [];

    protected MessageBag $messages;

    protected bool $validated = false;

    protected array $messageOverrides = [];

    protected bool $bailOnFirstError = false;

    public function __construct(MessageResolver $resolver, array $data, array $rules)
    {
        $this->resolver = $resolver;
        $this->data = $data;
        $this->messages = new MessageBag();
        $this->missingValue = 'missing' . Str::random(8);
        $this->add($rules);
    }

    public static function setFailureHandler(?Closure $handler): void
    {
        static::$failureHandler = $handler;
    }

    public function getMessages(): MessageBag
    {
        return $this->messages;
    }

    public function passes(): bool
    {
        $this->execute();

        return $this->messages->isEmpty();
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function validate(): array
    {
        if ($this->fails()) {
            $this->fail();
        }

        return $this->getValidatedData();
    }

    public function bailOnFirstError(bool $value = true): self
    {
        $this->bailOnFirstError = $value;

        return $this;
    }

    public function getValidatedData(): array
    {
        if (!$this->validated) {
            $this->execute();
        }

        return $this->validatedData;
    }

    public function usingMessageResolver(MessageResolver $resolver): self
    {
        $this->resolver = $resolver;

        return $this;
    }

    public function getMessageResolver(): MessageResolver
    {
        return $this->resolver;
    }

    /**
     * @param array $attributes
     *
     * Replace attributes with custom names.
     * Format ['pattern' => 'name'].
     *
     * @return static
     */
    public function override(array $attributes): self
    {
        $this->messageOverrides = $attributes;

        return $this;
    }

    public function getMessageOverrides(): array
    {
        return $this->messageOverrides;
    }

    public function getValueEntry(string $field): Entry
    {
        $value = Arr::get($this->data, $field, $this->missingValue);

        if ($value === $this->missingValue) {
            return new Entry($field, [], $field, null, false);
        }

        return new Entry($field, [], $field, $value, true);
    }

    public function add($rules): self
    {
        $entries = is_array($rules) ? $rules : [$rules];

        foreach ($entries as $entry) {
            if ($entry instanceof RuleSet) {
                $this->rules[] = $entry;
            } elseif ($entry instanceof RuleGroupContract) {
                foreach ($entry->getRules() as $rule) {
                    $this->add($rule);
                }
            } else {
                throw new InvalidRuleException('Unexpected rule encountered, expected RuleSet or RuleGroup.');
            }
        }

        return $this;
    }

    protected function execute(): void
    {
        $this->validated = false;
        $this->validatedData = [];
        $this->messages = new MessageBag();
        $originalRules = $this->rules;

        $ruleSetIndex = 0;
        $walker = new PathWalker($this->data);

        while (isset($this->rules[$ruleSetIndex])) {
            $ruleSet = $this->rules[$ruleSetIndex++];
            $pattern = $ruleSet->getPattern();
            $entryStack = new EntryPipelineCollection();

            foreach ($walker->walk($pattern) as $entry) {
                $entryPipeline = new EntryPipeline($this, $entry, $ruleSet->getRules());
                $entryStack->add($entryPipeline);
            }

            $this->processPipeline($entryStack);

            if ($this->bailOnFirstError && $this->messages->isNotEmpty()) {
                break;
            }
        }

        $this->rules = $originalRules;
        $this->validated = true;
    }

    protected function processPipeline(EntryPipelineCollection $pipeline): void
    {
        $pipeline->run();

        foreach ($pipeline->getPipelines() as $entryPipeline) {
            $messages = $entryPipeline->getMessages();

            if (empty($messages)) {
                if ($entryPipeline->shouldExtractData()) {
                    Arr::set($this->validatedData, $entryPipeline->getEntry()->getPath(), $entryPipeline->getEntry()->getValue());
                }
            } else {
                foreach ($messages as $message) {
                    $this->messages->add($message->getEntry()->getPath(), $this->resolver->resolve($message, $this));
                }
            }
        }
    }

    /**
     * @throws \DonnySim\Validation\Exceptions\ValidationException
     */
    protected function fail(): void
    {
        if (static::$failureHandler) {
            (static::$failureHandler)($this);
        } else {
            throw new ValidationException($this->getMessages());
        }
    }
}
