<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Traits;

use function is_array;
use function is_string;
use function trim;

trait EmptyCheckTrait
{
    protected function isEmpty($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }
}
