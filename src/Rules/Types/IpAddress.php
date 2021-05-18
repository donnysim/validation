<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Types;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Process\DataEntry;
use DonnySim\Validation\Process\ValidationProcess;
use function filter_var;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

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

    public function handle(ValidationProcess $process, DataEntry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        $value = $entry->getValue();

        if ($this->type === static::TYPE_MIXED) {
            if (filter_var($value, FILTER_VALIDATE_IP) === false) {
                $entry->addMessageAndFinish(static::NAME_MIXED);
            }

            return;
        }

        if ($this->type === static::TYPE_IPV4) {
            if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                $entry->addMessageAndFinish(static::NAME_IPV4);
            }

            return;
        }

        if (($this->type === static::TYPE_IPV6) && filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            $entry->addMessageAndFinish(static::NAME_IPV6);
        }
    }
}
