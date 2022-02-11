<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Traits;

use DonnySim\Validation\Tests\Stubs\TranslationMessageResolverStub;
use DonnySim\Validation\Validator;
use function count;

trait ValidationHelpersTrait
{
    protected function makeValidator(array $data, array $rules, array $messageOverrides = [], array $attributeOverrides = []): Validator
    {
        return new Validator($data, $rules, $messageOverrides, $attributeOverrides);
    }

    protected function assertValidationFail(Validator $validator, array $messages): void
    {
        self::assertFalse($validator->passes(), 'Validation should fail but passed.');
        $validationMessages = $validator->resolveMessages($this->makeValidationMessageResolver());
        self::assertCount(count($messages), $validationMessages);

        foreach ($messages as $key => $message) {
            self::assertArrayHasKey($key, $validationMessages);
            self::assertSame($message, $validationMessages[$key]);
        }
    }

    protected function makeValidationMessageResolver(): TranslationMessageResolverStub
    {
        return new TranslationMessageResolverStub([
            'accepted' => ':attribute must be accepted',
            'alpha' => ':attribute must be alpha',
            'alpha_dash' => ':attribute must be alpha dash',
            'alpha_num' => ':attribute must be alpha num',
            'array_type' => ':attribute must be array',
            'boolean_like' => ':attribute must be boolean like',
            'boolean_type' => ':attribute must be boolean',
            'confirmed' => ':attribute must be confirmed',
            'date' => ':attribute must be a date',
            'date_after' => ':attribute must be after :date',
            'date_after_or_equal' => ':attribute must be after or equal :date',
            'date_before' => ':attribute must be before :date',
            'date_before_or_equal' => ':attribute must be before or equal :date',
            'date_equal' => ':attribute must be equal :date',
            'date_format' => ':attribute must match :format',
            'different' => ':attribute must be different from :other',
            'digits' => ':attribute must have :digits digits',
            'digits_between' => ':attribute must have digits between :min and :max',
            'distinct' => ':attribute must be distinct',
            'email' => ':attribute must be email',
            'ends_with' => ':attribute must end with :values',
            'filled' => ':attribute must be filled',
            'greater_than.array' => ':attribute should contain more than :other items',
            'greater_than.numeric' => ':attribute should be greater than :other',
            'greater_than.string' => ':attribute should be greater than :other length',
            'greater_than_or_equal.array' => ':attribute should contain :other items',
            'greater_than_or_equal.numeric' => ':attribute should be min :other',
            'greater_than_or_equal.string' => ':attribute should be min :other length',
            'in' => ':attribute must be in array',
            'integer_type' => ':attribute must be integer',
            'ip_address.mixed' => ':attribute must be a valid ip address',
            'ip_address.ipv4' => ':attribute must be a valid ipv4 address',
            'ip_address.ipv6' => ':attribute must be a valid ipv6 address',
            'less_than.array' => ':attribute should contain less than :other items',
            'less_than.numeric' => ':attribute should be less than :other',
            'less_than.string' => ':attribute should be less than :other length',
            'less_than_or_equal.array' => ':attribute should contain max :other items',
            'less_than_or_equal.numeric' => ':attribute should be max :other',
            'less_than_or_equal.string' => ':attribute should be max :other length',
            'not_in' => ':attribute must not be in array',
            'numeric.float' => ':attribute must be numeric float',
            'numeric.integer' => ':attribute must be numeric integer',
            'numeric.mixed' => ':attribute must be numeric',
            'present' => ':attribute must be present',
            'required' => ':attribute is required',
            'starts_with' => ':attribute must start with :values',
            'string_type' => ':attribute must be string',
        ]);
    }
}
