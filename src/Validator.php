<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Contracts\MessageResolver;
use DonnySim\Validation\Contracts\RuleGroup;
use DonnySim\Validation\Contracts\RuleSet;
use DonnySim\Validation\Exceptions\InvalidRuleException;
use DonnySim\Validation\Exceptions\ValidationException;
use DonnySim\Validation\Process\ValidationProcess;
use function is_array;

class Validator
{
    protected static ?Closure $failureHandler = null;

    protected static ?MessageResolver $defaultMessageResolver = null;

    protected array $data;

    /**
     * @var \DonnySim\Validation\Contracts\RuleSet[]
     */
    protected array $rules = [];

    protected bool $validated = false;

    protected ?ValidationProcess $process = null;

    /**
     * @param array $data
     * @param \DonnySim\Validation\Contracts\RuleSet[] $rules
     *
     * @throws \DonnySim\Validation\Exceptions\InvalidRuleException
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->addRule($rules);
    }

    public static function setFailureHandler(?Closure $handler): void
    {
        static::$failureHandler = $handler;
    }

    public static function setDefaultMessageResolver(?MessageResolver $messageResolver): void
    {
        static::$defaultMessageResolver = $messageResolver;
    }

    /**
     * @throws \DonnySim\Validation\Exceptions\InvalidRuleException
     */
    public function addRule(RuleSet|RuleGroup|array $rules): static
    {
        $entries = is_array($rules) ? $rules : [$rules];

        foreach ($entries as $entry) {
            if ($entry instanceof RuleSet) {
                $this->rules[] = $entry;
            } elseif ($entry instanceof RuleGroup) {
                foreach ($entry->getRules() as $rule) {
                    $this->addRule($rule);
                }
            } else {
                throw new InvalidRuleException('Unexpected rule encountered, expected RuleSet or RuleGroup.');
            }
        }

        return $this;
    }

    public function passes(): bool
    {
        // TODO don't validate again
        $this->execute();

        return empty($this->getValidationMessages());
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return \DonnySim\Validation\Process\ValidationMessage[]
     */
    public function getValidationMessages(): array
    {
        if (!$this->process) {
            $this->execute();
        }

        return $this->process ? $this->process->getMessages() : [];
    }

    public function getValidatedData(): array
    {
        if (!$this->process) {
            $this->execute();
        }

        return $this->process->getValidatedData();
    }

    public function getMessages(?MessageResolver $messageResolver = null): mixed
    {
        $resolver = $messageResolver ?: static::$defaultMessageResolver;

        return $resolver ? $resolver->resolve($this->getValidationMessages()) : $this->getValidationMessages();
    }

    /**
     * @throws \DonnySim\Validation\Exceptions\ValidationException
     */
    public function validate(): void
    {
        if ($this->fails()) {
            $this->fail();
        }

        $this->execute();
    }

    public function getData(): array
    {
        return $this->data;
    }

    protected function execute(): void
    {
        $this->validated = false;

        $this->process = new ValidationProcess($this, $this->data, $this->rules);
        $this->process->run();

        $this->validated = true;
    }

    /**
     * @throws \DonnySim\Validation\Exceptions\ValidationException
     */
    protected function fail(): void
    {
        if (static::$failureHandler) {
            (static::$failureHandler)($this);
        } else {
            throw new ValidationException($this->getValidationMessages());
        }
    }
}
