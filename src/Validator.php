<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Exceptions\InvalidRuleException;
use DonnySim\Validation\Exceptions\ValidationException;
use DonnySim\Validation\Interfaces\MessageResolverInterface;
use DonnySim\Validation\Interfaces\RuleSetGroupInterface;
use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Process\ValidationProcess;

class Validator extends ArrayMessageResolver
{
    protected static ?MessageResolverInterface $defaultMessageResolver = null;

    protected static ?Closure $failureHandler = null;

    protected array $data;

    /**
     * @var array<\DonnySim\Validation\Interfaces\RuleSetInterface|\DonnySim\Validation\Interfaces\RuleSetGroupInterface>
     */
    protected array $rules = [];

    protected ?ValidationProcess $process = null;

    /**
     * @param array<\DonnySim\Validation\Interfaces\RuleSetInterface|\DonnySim\Validation\Interfaces\RuleSetGroupInterface> $rules
     *
     * @throws \DonnySim\Validation\Exceptions\InvalidRuleException
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->addRules($rules);
    }

    public static function setDefaultMessageResolver(?MessageResolverInterface $messageResolver): void
    {
        self::$defaultMessageResolver = $messageResolver;
    }

    public static function setFailureHandler(?Closure $handler): void
    {
        static::$failureHandler = $handler;
    }

    /**
     * @param array<\DonnySim\Validation\Interfaces\RuleSetInterface|\DonnySim\Validation\Interfaces\RuleSetGroupInterface> $rules
     *
     * @throws \DonnySim\Validation\Exceptions\InvalidRuleException
     */
    public function addRules(array $rules): self
    {
        foreach ($rules as $entry) {
            if ($entry instanceof RuleSetInterface) {
                $this->rules[] = $entry;
            } elseif ($entry instanceof RuleSetGroupInterface) {
                $this->addRules($entry->getRules());
            } else {
                throw new InvalidRuleException('Unexpected rule encountered, expected RuleSetInterface or RuleSetGroupInterface.');
            }
        }

        return $this;
    }

    /**
     * @throws \DonnySim\Validation\Exceptions\ValidationException
     */
    public function validate(): array
    {
        if ($this->fails()) {
            $this->fail();
        }

        return $this->getValidatedData();
    }

    public function getValidatedData(): array
    {
        return $this->getProcess()->getValidatedData();
    }

    public function passes(): bool
    {
        return empty($this->getMessages());
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return \DonnySim\Validation\Message[]
     */
    public function getMessages(): array
    {
        return $this->getProcess()->getMessages();
    }

    public function resolveMessages(?MessageResolverInterface $messageResolver): mixed
    {
        return ($messageResolver ?: self::$defaultMessageResolver ?: $this)->resolveMessage($this->getMessages());
    }

    protected function getProcess(): ValidationProcess
    {
        if (!$this->process) {
            $this->execute();
        }

        return $this->process;
    }

    protected function execute(): void
    {
        $this->process = new ValidationProcess($this->data, $this->rules);
        $this->process->run();
    }

    protected function reset(): void
    {
        $this->process = null;
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
