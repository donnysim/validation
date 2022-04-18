<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use DonnySim\Validation\Data\Arr;
use DonnySim\Validation\Message;

final class Result
{
    private array $validatedData = [];

    /**
     * @var \DonnySim\Validation\Message[]
     */
    private array $messages = [];

    public function addMessage(Message $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return \DonnySim\Validation\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function set(string $path, mixed $value): void
    {
        Arr::set($this->validatedData, $path, $value);
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function merge(Result $result, string $prefix = ''): void
    {
        foreach ($result->getMessages() as $message) {
            if ($prefix) {
                $this->addMessage($message->withPrefix($prefix));
            } else {
                $this->addMessage($message);
            }
        }

        foreach ($result->getValidatedData() as $key => $value) {
            if ($prefix) {
                $this->set("{$prefix}{$key}", $value);
            } else {
                $this->set($key, $value);
            }
        }
    }
}
