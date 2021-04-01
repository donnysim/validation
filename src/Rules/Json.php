<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Data\Entry;
use DonnySim\Validation\Data\EntryPipeline;
use Exception;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class Json implements SingleRule
{
    public const NAME = 'json';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        try {
            json_decode($entry->getValue(), false, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            $pipeline->fail(static::NAME);
        }
    }
}
