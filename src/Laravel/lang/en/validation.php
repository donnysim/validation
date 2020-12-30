<?php

declare(strict_types=1);

return [
    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array_type' => 'The :attribute must be an array.',
    'between' => [
        'array' => 'The :attribute must have between :min and :max items.',
        'numeric' => 'The :attribute must be between :min and :max.',
        'string' => 'The :attribute must be between :min and :max characters.',
    ],
    'boolean_like' => 'The :attribute must represent a boolean value.',
    'boolean_type' => 'The :attribute must be of type boolean.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_after' => 'The :attribute must be a date after :date.',
    'date_after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'date_before' => 'The :attribute must be a date before :date.',
    'date_before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'date_equal' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'filled' => 'The :attribute field must have a value.',
    'greater_than' => [
        'array' => 'The :attribute must have more than :min items.',
        'numeric' => 'The :attribute must be greater than :min.',
        'string' => 'The :attribute must be longer than :min characters.',
    ],
    'greater_than_or_equal' => [
        'array' => 'The :attribute must have at least :min items.',
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'in' => 'The selected :attribute is invalid.',
    'integer_type' => 'The :attribute must be of type integer.',
    'ip_address' => [
        'mixed' => 'The :attribute must be a valid IP address.',
        'ipv4' => 'The :attribute must be a valid IPv4 address.',
        'ipv6' => 'The :attribute must be a valid IPv6 address.',
    ],
    'json' => 'The :attribute must be a valid JSON string.',
    'less_than' => [
        'array' => 'The :attribute must have less than :max items.',
        'numeric' => 'The :attribute must be less than :max.',
        'string' => 'The :attribute must be less than :max characters.',
    ],
    'less_than_or_equal' => [
        'array' => 'The :attribute may not have more than :max items.',
        'numeric' => 'The :attribute may not be greater than :max.',
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => [
        'float' => 'The :attribute must be a numeric float.',
        'integer' => 'The :attribute must be a numeric integer.',
        'mixed' => 'The :attribute must be numeric.',
    ],
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'same' => 'The :attribute and :other must match.',
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string_type' => 'The :attribute must be of type string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',
];
