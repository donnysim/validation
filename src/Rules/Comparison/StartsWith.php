<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Comparison;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function implode;
use function str_starts_with;

class StartsWith implements SingleRule
{
    public const NAME = 'starts_with';

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

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $haystack = $entry->getValue();
        foreach ($this->needles as $needle) {
            if ($needle !== '' && str_starts_with($haystack, $needle)) {
                return;
            }
        }

        $entry->addMessageAndFinish(static::NAME, ['values' => implode(', ', $this->needles)]);
    }
}
