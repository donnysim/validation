<?php

declare(strict_types=1);

namespace DonnySim\Validation\Laravel;

use DonnySim\Validation\Contracts\MessageOverrideProvider;
use DonnySim\Validation\Contracts\MessageResolver;
use DonnySim\Validation\Message;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;

class TranslationMessageResolver implements MessageResolver
{
    protected TranslatorContract $translator;

    public function __construct(TranslatorContract $translator)
    {
        $this->translator = $translator;
    }

    public function resolve(Message $message, MessageOverrideProvider $provider): string
    {
        $overrides = $provider->getMessageOverrides();
        $path = $message->getEntry()->getPath();

        $attribute = $path;

        if (isset($overrides[$path])) {
            $attribute = $overrides[$path];
        } elseif (isset($overrides[$message->getEntry()->getPattern()])) {
            $attribute = $overrides[$message->getEntry()->getPattern()];
        }

        return $this->translator->get(
            "donnysim::validation.{$message->getKey()}",
            \array_merge(\compact('path', 'attribute'), $message->getParams())
        );
    }
}
