<?php

declare(strict_types=1);

namespace DonnySim\Validation;

use DonnySim\Validation\Interfaces\MessageOverrideProviderInterface;

class MessageOverrideProvider implements MessageOverrideProviderInterface
{
    /**
     * @var array<string>
     */
    protected array $messageOverrides;

    /**
     * @var array<string>
     */
    protected array $attributeOverrides;

    /**
     * @param array<string, string> $messageOverrides
     * @param array<string, string> $attributeOverrides
     */
    public function __construct(array $messageOverrides, array $attributeOverrides)
    {
        $this->messageOverrides = $messageOverrides;
        $this->attributeOverrides = $attributeOverrides;
    }

    public function getMessageOverride(Message $message): ?string
    {
        return $this->messageOverrides["{$message->getPath()}.{$message->getFailingRuleName()}"] ??
            $this->messageOverrides["{$message->getPattern()}.{$message->getFailingRuleName()}"] ??
            null;
    }

    public function getAttributeOverride(Message $message): string
    {
        return $this->attributeOverrides[$message->getPath()] ??
            $this->attributeOverrides[$message->getPattern()] ??
            $message->getAttribute();
    }
}
