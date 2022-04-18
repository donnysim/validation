<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Stubs;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Validator;

class NestedRule implements RuleInterface
{
    public function validate(DataEntry $entry, ValidationProcess $process): void
    {
        $validator = (new Validator($entry->getValue(), [
            RuleSet::make('name')->required(),
        ]));

        if ($validator->fails()) {
            $process->getResult()->merge($validator->getResult(), $entry->getPath() . '.');

            $process->getCurrent()->fail(Message::forEntry($entry, 'NESTED_RULE'));
        }
    }
}
