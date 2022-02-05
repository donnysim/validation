<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity\Email;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Interfaces\RuleInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\EntryProcess;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\Extra\SpoofCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use function is_string;

final class Email implements RuleInterface
{
    public const NAME = 'email';

    public const VALIDATE_RFC = 'rfc';

    public const VALIDATE_STRICT = 'strict';

    public const VALIDATE_DNS = 'dns';

    public const VALIDATE_SPOOF = 'spoof';

    public const VALIDATE_FILTER = 'filter';

    public const VALIDATE_FILTER_UNICODE = 'filter_unicode';

    /**
     * @var string[]
     */
    protected array $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function validate(DataEntry $entry, EntryProcess $process): void
    {
        if ($entry->isNotPresent()) {
            return;
        }

        if (!is_string($entry->getValue()) || !$this->validateEmail($entry->getValue())) {
            $process->fail(Message::forEntry($entry, self::NAME));
        }
    }

    protected function validateEmail(string $value): bool
    {
        $rules = [];

        foreach ($this->types as $type) {
            switch ($type) {
                case self::VALIDATE_RFC:
                    $rules[] = new RFCValidation();
                    break;
                case self::VALIDATE_STRICT:
                    $rules[] = new NoRFCWarningsValidation();
                    break;
                case self::VALIDATE_DNS:
                    $rules[] = new DNSCheckValidation();
                    break;
                case self::VALIDATE_SPOOF:
                    $rules[] = new SpoofCheckValidation();
                    break;
                case self::VALIDATE_FILTER:
                    $rules[] = new FilterEmailValidation();
                    break;
                case self::VALIDATE_FILTER_UNICODE:
                    $rules[] = FilterEmailValidation::unicode();
                    break;
            }
        }

        if (empty($rules)) {
            $rules[] = new RFCValidation();
        }

        return (new EmailValidator())->isValid($value, new MultipleValidationWithAnd($rules));
    }
}
