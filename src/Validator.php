<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use DonnySim\Validation\Contracts\MessageResolver;

class Validator
{
    protected MessageResolver $resolver;

    protected string $missingValue;

    protected array $data;

    /**
     * @var \DonnySim\Validation\Rules[]
     */
    protected array $rules = [];

    protected array $validatedData = [];

    /**
     * @var \DonnySim\Validation\Pipeline[]
     */
    protected array $pipelines = [];

    protected MessageBag $messages;

    protected bool $validated = false;

    public function __construct(MessageResolver $resolver, array $data, array $rules, array $attributeNames = [])
    {
        $this->resolver = $resolver;
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = new MessageBag();
        $this->missingValue = 'missing' . Str::random(8);
        $resolver->setAttributeNames($attributeNames);

        // TODO prevent adding multiple rules with same pattern?
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

    public function getValidatedData(): array
    {
        if (!$this->validated) {
            $this->execute();
        }

        return $this->validatedData;
    }

    public function getMessageResolver(): MessageResolver
    {
        return $this->resolver;
    }

    public function getValueEntry(string $field): Entry
    {
        $value = Arr::get($this->data, $field, $this->missingValue);

        if ($value === $this->missingValue) {
            return new Entry($field, [], $field, null, false);
        }

        return new Entry($field, [], $field, $value, true);
    }

    protected function execute(): void
    {
        $this->validatedData = [];
        $this->pipelines = [];
        $this->messages = new MessageBag();

        foreach ($this->rules as $ruleSet) {
            $pattern = $ruleSet->getPattern();

            if (!isset($this->pipelines[$pattern])) {
                $this->pipelines[$pattern] = new Pipeline();
            }

            $walker = new PathWalker($this->data);
            $walker->onHit(function (string $path, $value, array $wildcards) use ($ruleSet, $pattern) {
                $entry = new Entry($pattern, $wildcards, $path, $value, true);
                $entryPipeline = new EntryPipeline($this, $entry, $ruleSet->getRules());

                $this->pipelines[$pattern]->add($entryPipeline);
            });
            $walker->onMiss(function (string $path, array $wildcards) use ($ruleSet, $pattern) {
                $entry = new Entry($pattern, $wildcards, $path, null, false);
                $entryPipeline = new EntryPipeline($this, $entry, $ruleSet->getRules());

                $this->pipelines[$pattern]->add($entryPipeline);
            });

            $walker->walk($pattern);
        }

        foreach ($this->pipelines as $pipeline) {
            $this->processPipeline($pipeline);
        }

        $this->validated = true;
    }

    protected function processPipeline(Pipeline $pipeline): void
    {
        $pipeline->run();

        foreach ($pipeline->getEntryPipelines() as $entryPipeline) {
            $messages = $entryPipeline->getMessages();

            if (empty($messages)) {
                if ($entryPipeline->shouldExtractData()) {
                    Arr::set($this->validatedData, $entryPipeline->getEntry()->getPath(), $entryPipeline->getEntry()->getValue());
                }
            } else {
                foreach ($messages as $message) {
                    $this->messages->add($message->getEntry()->getPath(), $this->resolver->resolve($message));
                }
            }
        }
    }
}
