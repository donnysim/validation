<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use Exception;
use function count;
use function dns_get_record;
use function is_string;
use function parse_url;
use const DNS_A;
use const DNS_AAAA;
use const PHP_URL_HOST;

class ActiveUrl implements SingleRule
{
    public const NAME = 'active_url';

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();
        if (!is_string($value)) {
            $entry->addMessageAndFinish(static::NAME);
            return;
        }

        $url = parse_url($value, PHP_URL_HOST);

        if ($url) {
            try {
                if (count(dns_get_record($url, DNS_A | DNS_AAAA)) > 0) {
                    return;
                }
            } catch (Exception) {
                $entry->addMessageAndFinish(static::NAME);
            }
        }

        $entry->addMessageAndFinish(static::NAME);
    }
}
