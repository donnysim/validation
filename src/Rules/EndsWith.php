<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use function implode;
use function substr;

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
            if ($needle !== '' && substr($haystack, -mb_strlen($needle)) === $needle) {
                return;
            }
        }

        $pipeline->fail(static::NAME, ['values' => implode(', ', $this->needles)]);
    }
}
