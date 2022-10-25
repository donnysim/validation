<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use Closure;
use DonnySim\Validation\Exceptions\InvalidRuleException;
use DonnySim\Validation\Exceptions\ValidationException;
use DonnySim\Validation\Interfaces\MessageOverrideProviderInterface;
use DonnySim\Validation\Interfaces\MessageResolverInterface;
use DonnySim\Validation\Interfaces\RuleSetGroupInterface;
use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Process\Result;
use DonnySim\Validation\Process\ValidationProcess;

class Validator
{
    protected static ?Closure $defaultMessageResolverFactory = null;

    protected static ?Closure $defaultMessageProviderFactory = null;

    protected static ?Closure $failureHandler = null;

    protected array $data;

    /**
     * @var array<\DonnySim\Validation\Interfaces\RuleSetInterface|\DonnySim\Validation\Interfaces\RuleSetGroupInterface>
     */
    protected array $rules = [];

    protected MessageResolverInterface $messageResolver;

    protected MessageOverrideProviderInterface $overrideProvider;

    protected ?Result $result = null;

    /**
     * @param array<\DonnySim\Validation\Interfaces\RuleSetInterface|\DonnySim\Validation\Interfaces\RuleSetGroupInterface> $rules
     * @param array<string, string> $messageOverrides
     * @param array<string, string> $attributeOverrides
     *
     * @throws \DonnySim\Validation\Exceptions\InvalidRuleException
     */
    public function __construct(array $data, array $rules, array $messageOverrides = [], array $attributeOverrides = [])
    {
        $this->data = $data;
        $this->messageResolver = static::makeMessageResolver();
        $this->overrideProvider = static::makeOverrideProvider($messageOverrides, $attributeOverrides);
        $this->addRules($rules);
    }

    public static function setMessageResolverFactory(?Closure $factory): void
    {
        self::$defaultMessageResolverFactory = $factory;
    }

    public static function setOverrideProviderFactory(?Closure $factory): void
    {
        self::$defaultMessageProviderFactory = $factory;
    }

    public static function makeMessageResolver(array $messageOverrides = [], array $attributeOverrides = []): MessageResolverInterface
    {
        if (self::$defaultMessageResolverFactory) {
            return (self::$defaultMessageResolverFactory)($messageOverrides, $attributeOverrides);
        }

        return new ArrayMessageResolver();
    }

    public static function makeOverrideProvider(array $messageOverrides = [], array $attributeOverrides = []): MessageOverrideProviderInterface
    {
        if (self::$defaultMessageProviderFactory) {
            return (self::$defaultMessageProviderFactory)($messageOverrides, $attributeOverrides);
        }

        return new MessageOverrideProvider($messageOverrides, $attributeOverrides);
    }

    public static function setFailureHandler(?Closure $handler): void
    {
        static::$failureHandler = $handler;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
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
        return $this->getResult()->getValidatedData();
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
        return $this->getResult()->getMessages();
    }

    public function resolveMessages(?MessageResolverInterface $messageResolver = null): mixed
    {
        return ($messageResolver ?: $this->messageResolver)->resolveMessages($this->getMessages(), $this->overrideProvider);
    }

    public function reset(): void
    {
        $this->result = null;
    }

    public function getResult(): Result
    {
        if (!$this->result) {
            $this->result = (new ValidationProcess($this->data, $this->rules))->run()->getResult();
        }

        return $this->result;
    }

    /**
     * @throws \DonnySim\Validation\Exceptions\ValidationException
     */
    protected function fail(): void
    {
        if (static::$failureHandler) {
            (static::$failureHandler)($this);
        } else {
            throw new ValidationException($this);
        }
    }
}
