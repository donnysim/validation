<?php

declare(strict_types=1);

namespace DonnySim\Validation\Process;

use function preg_replace_callback;

final class DataEntry
{
    protected mixed $value;

    protected bool $exists;

    protected string $path;

    protected array $messages = [];

    protected array $wildcards;

    protected string $pattern;

    protected ?DataEntryPipeline $pipeline = null;

    public function __construct(string $pattern, array $wildcards, string $path, $value, bool $exists)
    {
        $this->path = $path;
        $this->value = $value;
        $this->exists = $exists;
        $this->wildcards = $wildcards;
        $this->pattern = $pattern;
    }

    public function setPipeline(?DataEntryPipeline $pipeline): void
    {
        $this->pipeline = $pipeline;
    }

    public function getPipeline(): ?DataEntryPipeline
    {
        return $this->pipeline;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function isMissing(): bool
    {
        return !$this->exists;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function finish(): void
    {
        $this->pipeline->finish();
    }

    public function getWildcards(): array
    {
        return $this->wildcards;
    }

    public function addMessage(string $key, array $params = []): void
    {
        $this->messages[] = new ValidationMessage($this, $key, $params);
    }

    public function addMessageAndFinish(string $key, array $params = []): void
    {
        $this->addMessage($key, $params);
        $this->finish();
    }

    /**
     * @return \DonnySim\Validation\Process\ValidationMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function resolveSegmentWildcards(string $path): string
    {
        $index = 0;

        return preg_replace_callback('/\*/', function () use (&$index) {
            return $this->wildcards[$index++] ?? '*';
        }, $path);
    }
}
