<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Stubs;

use DonnySim\Validation\Contracts\MessageResolver;
use DonnySim\Validation\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TestMessageResolver implements MessageResolver
{
    protected array $messages = [];

    protected array $attributeNames = [];

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function resolve(Message $message): string
    {
        $path = $message->getEntry()->getPath();
        $key = $message->getKey();

        if (!isset($this->messages[$key])) {
            return 'missing message';
        }

        $attribute = $path;

        if (isset($this->attributeNames[$path])) {
            $attribute = $this->attributeNames[$path];
        } elseif (isset($this->attributeNames[$message->getEntry()->getPattern()])) {
            $attribute = $this->attributeNames[$message->getEntry()->getPattern()];
        }

        return $this->makeReplacements(
            $this->messages[$key],
            \array_merge(\compact('path', 'attribute'), $message->getParams())
        );
    }

    public function setAttributeNames(array $attributes): void
    {
        $this->attributeNames = $attributes;
    }

    protected function sortReplacements(array $replace): array
    {
        return (new Collection($replace))->sortBy(function ($value, $key) {
            return mb_strlen($key) * -1;
        })->all();
    }

    protected function makeReplacements($line, array $replace)
    {
        if (empty($replace)) {
            return $line;
        }

        $replace = $this->sortReplacements($replace);

        foreach ($replace as $key => $value) {
            $line = \str_replace(
                [':' . $key, ':' . Str::upper($key), ':' . Str::ucfirst($key)],
                [$value, Str::upper($value), Str::ucfirst($value)],
                $line
            );
        }

        return $line;
    }
}
