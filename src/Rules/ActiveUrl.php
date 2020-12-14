<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use Exception;

class ActiveUrl implements SingleRule
{
    public const NAME = 'active_url';

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if (! \is_string($value)) {
            $pipeline->fail(static::NAME);
            return;
        }

        if ($url = \parse_url($value, \PHP_URL_HOST)) {
            try {
                if (\count(\dns_get_record($url, \DNS_A | \DNS_AAAA)) > 0) {
                    return;
                }
            } catch (Exception $e) {
                $pipeline->fail(static::NAME);
            }
        }

        $pipeline->fail(static::NAME);
    }
}
