<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use Exception;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class Json implements SingleRule
{
    public const NAME = 'json';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        try {
            json_decode($entry->getValue(), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            $entry->addMessageAndFinish(static::NAME);
        }
    }
}
