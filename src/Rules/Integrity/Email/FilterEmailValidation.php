<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Integrity\Email;

use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;
use function filter_var;
use const FILTER_FLAG_EMAIL_UNICODE;
use const FILTER_VALIDATE_EMAIL;

final class FilterEmailValidation implements EmailValidation
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
        return new self(FILTER_FLAG_EMAIL_UNICODE);
    }

    public function isValid($email, EmailLexer $emailLexer): bool
    {
        if ($this->flags === null) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL, $this->flags) !== false;
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
