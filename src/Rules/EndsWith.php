<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class EndsWith implements SingleRule
{
    public const NAME = 'ends_with';

    /**
     * @var string[]
     */
    protected array $needles;

    /**
     * @param string[] $needles
     */
    public function __construct(array $needles)
    {
        $this->needles = $needles;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $haystack = $entry->getValue();
        foreach ($this->needles as $needle) {
            if ($needle !== '' && \substr($haystack, -\strlen($needle)) === $needle) {
                return;
            }
        }

        $pipeline->fail(static::NAME, ['values' => \implode(', ', $this->needles)]);
    }
}
