<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use function in_array;

final class In implements RuleInterface
{
    public const NAME_IN = 'in';
    public const NAME_NOT_IN = 'not_in';

    protected array $values;

    protected bool $bool;

    public function __construct(array $values, bool $bool = true)
    {
        $this->values = $values;
        $this->bool = $bool;
    }

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        if (in_array($entry->getValue(), $this->values, true) === !$this->bool) {
            $process->fail(Message::forEntry($entry, $this->bool ? self::NAME_IN : self::NAME_NOT_IN));
        }
    }
}
