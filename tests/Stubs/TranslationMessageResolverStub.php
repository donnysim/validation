<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Stubs;

use DonnySim\Validation\Interfaces\MessageOverrideProviderInterface;
use DonnySim\Validation\Interfaces\MessageResolverInterface;
use function array_keys;
use function array_merge;
use function asort;
use function mb_strlen;
use function mb_strtoupper;
use function mb_substr;
use function str_replace;
use const SORT_REGULAR;

final class TranslationMessageResolverStub implements MessageResolverInterface
{
    protected array $messages;

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function resolveMessages(array $messages, MessageOverrideProviderInterface $overrideProvider): array
    {
        $result = [];

        foreach ($messages as $message) {
            $line = $this->messages[$message->getFailingRuleName()] ?? 'missing message';
            $overriddenMessage = $overrideProvider->getMessageOverride($message);

            if ($overriddenMessage) {
                $line = $overriddenMessage;
            }

            $path = $message->getPath();
            $result[$path][] = $this->makeReplacements(
                $line,
                array_merge([
                    'path' => $path,
                    'attribute' => $overrideProvider->getAttributeOverride($message),
                ], $message->getParams())
            );
        }

        return $result;
    }

    protected function makeReplacements($line, array $replace)
    {
        if (empty($replace)) {
            return $line;
        }

        $replace = $this->sortReplacements($replace);

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':' . $key, ':' . mb_strtoupper($key, 'UTF-8'), ':' . $this->ucfirst($key)],
                [$value, mb_strtoupper((string)$value, 'UTF-8'), $this->ucfirst((string)$value)],
                $line
            );
        }

        return $line;
    }

    protected function sortReplacements(array $replace): array
    {
        $results = [];

        foreach ($replace as $key => $value) {
            $results[$key] = mb_strlen($key) * -1;
        }

        asort($results, SORT_REGULAR);

        // Once we have sorted all the keys in the array, we will loop through them
        // and grab the corresponding model, so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $replace[$key];
        }

        return $results;
    }

    protected function ucfirst(string $string): string
    {
        return mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($string, 1, null, 'UTF-8');
    }
}
