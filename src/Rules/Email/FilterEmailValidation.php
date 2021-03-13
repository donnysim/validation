<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Email;

use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;
use function filter_var;
use function is_null;
use const FILTER_FLAG_EMAIL_UNICODE;
use const FILTER_VALIDATE_EMAIL;

class FilterEmailValidation implements EmailValidation
{
    /**
     * The flags to pass to the filter_var function.
     */
    protected ?int $flags;

    public function __construct(?int $flags = null)
    {
        $this->flags = $flags;
    }

    public static function unicode(): self
    {
        return new static(FILTER_FLAG_EMAIL_UNICODE);
    }

    public function isValid($email, EmailLexer $emailLexer): bool
    {
        return is_null($this->flags)
            ? filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            : filter_var($email, FILTER_VALIDATE_EMAIL, $this->flags) !== false;
    }

    public function getError(): ?InvalidEmail
    {
        return null;
    }

    public function getWarnings(): array
    {
        return [];
    }
}
