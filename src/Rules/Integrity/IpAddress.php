<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use function filter_var;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

final class IpAddress implements RuleInterface
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
        $this->type = $type ?: self::TYPE_MIXED;
    }

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        $value = $entry->getValue();

        if ($this->type === self::TYPE_MIXED) {
            if (filter_var($value, FILTER_VALIDATE_IP) === false) {
                $process->fail(Message::forEntry($entry, self::NAME_MIXED));
            }

            return;
        }

        if ($this->type === self::TYPE_IPV4) {
            if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                $process->fail(Message::forEntry($entry, self::NAME_IPV4));
            }

            return;
        }

        if (($this->type === self::TYPE_IPV6) && filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            $process->fail(Message::forEntry($entry, self::NAME_IPV6));
        }
    }
}
