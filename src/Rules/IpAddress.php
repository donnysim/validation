<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class IpAddress implements SingleRule
{
    public const NAME = 'ip_address';
    public const TYPE_ALL = 'All';
    public const TYPE_IPV4 = 'Ipv4';
    public const TYPE_IPV6 = 'Ipv6';

    protected string $type;

    public function __construct(?string $type)
    {
        $this->type = $type ?: static::TYPE_ALL;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if ($this->type === static::TYPE_ALL) {
            if (\filter_var($value, \FILTER_VALIDATE_IP) === false) {
                $pipeline->fail(static::NAME);
            }

            return;
        }

        if ($this->type === static::TYPE_IPV4) {
            if (\filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) === false) {
                $pipeline->fail(static::NAME);
            }

            return;
        }

        if ($this->type === static::TYPE_IPV6) {
            if (\filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) === false) {
                $pipeline->fail(static::NAME);
            }

            return;
        }
    }
}
