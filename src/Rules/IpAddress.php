<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class IpAddress implements SingleRule
{
    public const NAME_MIXED = 'ip_address.mixed';
    public const NAME_IPV4 = 'ip_address.ipv4';
    public const NAME_IPV6 = 'ip_address.ipv6';

    public const TYPE_MIXED = 'Mixed';
    public const TYPE_IPV4 = 'Ipv4';
    public const TYPE_IPV6 = 'Ipv6';

    protected string $type;

    public function __construct(?string $type)
    {
        $this->type = $type ?: static::TYPE_MIXED;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if ($this->type === static::TYPE_MIXED) {
            if (\filter_var($value, \FILTER_VALIDATE_IP) === false) {
                $pipeline->fail(static::NAME_MIXED);
            }

            return;
        }

        if ($this->type === static::TYPE_IPV4) {
            if (\filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) === false) {
                $pipeline->fail(static::NAME_IPV4);
            }

            return;
        }

        if ($this->type === static::TYPE_IPV6) {
            if (\filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) === false) {
                $pipeline->fail(static::NAME_IPV6);
            }

            return;
        }
    }
}
