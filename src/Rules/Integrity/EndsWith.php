<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use function implode;
use function str_ends_with;

final class EndsWith implements RuleInterface
{
    public const NAME = 'ends_with';

    /**
     * @var array<string>
     */
    protected array $needles;

    /**
     * @param array<string> $needles
     */
    public function __construct(array $needles)
    {
        $this->needles = $needles;
    }

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $haystack = $entry->getValue();
        foreach ($this->needles as $needle) {
            if ($needle !== '' && str_ends_with($haystack, $needle)) {
                return;
            }
        }

        $process->fail(Message::forEntry($entry, self::NAME, ['values' => $this->needles]));
    }
}
