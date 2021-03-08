<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Email;

use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules\Email\FilterEmailValidation;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;
use function is_string;

class Email implements SingleRule
{
    public const VALIDATE_RFC = 'rfc';
    public const VALIDATE_STRICT = 'strict';
    public const VALIDATE_DNS = 'dns';
    public const VALIDATE_SPOOF = 'spoof';
    public const VALIDATE_FILTER = 'filter';
    public const VALIDATE_FILTER_UNICODE = 'filter_unicode';
    public const NAME = 'email';

    /**
     * @var string[]
     */
    protected array $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function handle(EntryPipeline $pipeline, Entry $entry): void
    {
        if ($entry->isMissing()) {
            return;
        }

        if (!is_string($entry->getValue()) || !$this->validateEmail($entry->getValue())) {
            $pipeline->fail(static::NAME);
        }
    }

    protected function validateEmail(string $value): bool
    {
        $rules = [];

        foreach ($this->types as $type) {
            switch ($type) {
                case static::VALIDATE_RFC:
                    $rules[] = new RFCValidation();
                    break;
                case static::VALIDATE_STRICT:
                    $rules[] = new NoRFCWarningsValidation();
                    break;
                case static::VALIDATE_DNS:
                    $rules[] = new DNSCheckValidation();
                    break;
                case static::VALIDATE_SPOOF:
                    $rules[] = new SpoofCheckValidation();
                    break;
                case static::VALIDATE_FILTER:
                    $rules[] = new FilterEmailValidation();
                    break;
                case static::VALIDATE_FILTER_UNICODE:
                    $rules[] = FilterEmailValidation::unicode();
                    break;
            }
        }

        if (empty($rules)) {
            $rules[] = new RFCValidation();
        }

        $emailValidator = new EmailValidator();

        return $emailValidator->isValid($value, new MultipleValidationWithAnd($rules));
    }
}
