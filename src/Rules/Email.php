<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules;

use DonnySim\Validation\Rules\Email\FilterEmailValidation;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;
use DonnySim\Validation\Contracts\SingleRule;
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;

class Email implements SingleRule
{
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

        if (!\is_string($entry->getValue()) || !$this->validateEmail($entry->getValue())) {
            $pipeline->fail(static::NAME);
        }
    }

    protected function validateEmail(string $value): bool
    {
        $rules = [];

        foreach ($this->types as $type) {
            switch ($type) {
                case 'rfc':
                    $rules[] = new RFCValidation();
                    break;
                case 'strict':
                    $rules[] = new NoRFCWarningsValidation();
                    break;
                case 'dns':
                    $rules[] = new DNSCheckValidation();
                    break;
                case 'spoof':
                    $rules[] = new SpoofCheckValidation();
                    break;
                case 'filter':
                    $rules[] = new FilterEmailValidation();
                    break;
                case 'filter_unicode':
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
