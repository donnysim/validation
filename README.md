# Validator

Proof of concept of my validator idea, kind of based/inspired by Laravel.

## Installation

Just don't, everything is subject to change or this might just be deleted. I'm unsure about meany things including rule naming, rule array - [rule('email')]
vs ['email' => rule()->] etc.

## Validation

The validator executes rules in the same order they were passed in.

```php
<?php

use DonnySim\Validation\Validator;
use function DonnySim\Validation\rule;

$validator = new Validator(
    $messageResolver,
    [
        'email' => 'no-email',
        'roles' => [
            ['id' => 1, 'name' => 'Developer'],
            ['id' => '2', 'name' => 'Admin'],
        ],
        'agreement' => 'yes',
    ],
    [
        rule('email')->required()->email(),
        rule('blocked')->setValueIfMissing(false)->booleanType(),
        rule('roles', false)->required()->greaterThanOrEqual(1),
        // OR rule('roles')->omitFromData()->required()->min(1),
        rule('roles.*.id')->required()->integerType(),
        rule('agreement')->required()->accepted()->castToBoolean(),
    ]
);

$data = $validator->getValidatedData();
//[
//    'blocked' => false,
//    'roles' => [
//        ['id' => 1],
//    ],
//    'agreement' => true,
//];
```

Referencing other fields uses `field_reference`, e.g. `rule('start_date')->before(field_reference('end_date'))`. Support for references depends on rules, and some rules might
accept other field name as an argument e.g. `same('other_field')`. Referenced field values are provided as is from data without any validation passes.

You can use your own rule via `->rule($myRule)`.

The target is to have a validator with no unexpected behavior like a string '1' passes an integer rule. This is build for data integrity and flexibility not for
performance/insanely fast validation.

## Basics

Each rule is a separate class, it implements either `\DonnySim\Validation\Contracts\SingleRule` or
`\DonnySim\Validation\Contracts\BatchRule`, for examples you can just look up the `Rules` directory.
(there are no `BatchRule` currently).

Translations are provided via `\DonnySim\Validation\Contracts\MessageResolver` contract, e.g. for Laravel it could be:

```php
<?php

use DonnySim\Validation\Message;
use DonnySim\Validation\Contracts\MessageResolver;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;

class TranslationResolver implements MessageResolver
{
    protected TranslatorContract $translator;

    public function __construct(TranslatorContract $translator)
    {
        $this->translator = $translator;
    }

    public function resolve(Message $message): string
    {
        $path = $message->getEntry()->getPath();

        return $this->translator->get(
            "validation.{$message->getKey()}",
            ['attribute' => $path] + $message->getParams()
        );
    }
}
```

Thought the validation message keys might not match the ones provided by Laravel.

## TODO

- Decide if it's for Laravel or more general validator.

  For laravel we could include service provider, validation message replacements, rules related to laravel like exists etc.

## Available Validation Rules

Below is a list of all available validation rules and their function:

<!-- table-rule-start -->

| A | B | C | D | E | F |
| :--- | :--- | :--- | :--- | :--- | :--- |
| [Accepted](#accepted) | [Between](#between) | [Cast To Boolean](#cast-to-boolean) | [Date](#date) | [Email](#email) | [Filled](#filled) |
| [Active Url](#active-url) | [Boolean Like](#boolean-like) | [Confirmed](#confirmed) | [Date After](#date-after) | [Ends With](#ends-with) |  |
| [Alpha](#alpha) | [Boolean Type](#boolean-type) |  | [Date After Or Equal](#date-after-or-equal) |  |  |
| [Alpha Dash](#alpha-dash) |  |  | [Date Before](#date-before) |  |  |
| [Alpha Num](#alpha-num) |  |  | [Date Before Or Equal](#date-before-or-equal) |  |  |
| [Array Type](#array-type) |  |  | [Date Equal](#date-equal) |  |  |
|  |  |  | [Date Format](#date-format) |  |  |
|  |  |  | [Different](#different) |  |  |
|  |  |  | [Digits](#digits) |  |  |
|  |  |  | [Digits Between](#digits-between) |  |  |
|  |  |  | [Distinct](#distinct) |  |  |

| G | I | J | L | N | O |
| :--- | :--- | :--- | :--- | :--- | :--- |
| [Greater Than](#greater-than) | [In](#in) | [Json](#json) | [Less Than](#less-than) | [Not In](#not-in) | [Omit From Data](#omit-from-data) |
| [Greater Than Or Equal](#greater-than-or-equal) | [Integer Type](#integer-type) |  | [Less Than Or Equal](#less-than-or-equal) | [Not Regex](#not-regex) |  |
|  | [Ip Address](#ip-address) |  |  | [Nullable](#nullable) |  |
|  |  |  |  | [Numeric](#numeric) |  |
|  |  |  |  | [Numeric Float](#numeric-float) |  |
|  |  |  |  | [Numeric Integer](#numeric-integer) |  |

| P | R | S | T | U | W |
| :--- | :--- | :--- | :--- | :--- | :--- |
| [Pipe](#pipe) | [Regex](#regex) | [Same](#same) | [Timezone](#timezone) | [Url](#url) | [When](#when) |
| [Present](#present) | [Required](#required) | [Set Value If Missing](#set-value-if-missing) |  | [Uuid](#uuid) |  |
|  | [Rule](#rule) | [Sometimes](#sometimes) |  |  |  |
|  | [Rules](#rules) | [Starts With](#starts-with) |  |  |  |
|  |  | [String Type](#string-type) |  |  |  |

<!-- table-rule-end -->

## Rules

### Accepted

The field under validation must be `"yes"`, `"on"`, `1`, or `true`.
This is useful for validating "Terms of Service" acceptance or similar fields.

### Active Url

The field under validation must have a valid A or AAAA record according to the `dns_get_record` PHP function.
The hostname of the provided URL is extracted using the `parse_url` PHP function before being passed to `dns_get_record`.

### Alpha

The field under validation must be entirely alphabetic characters.

### Alpha Dash

The field under validation may have alpha-numeric characters, as well as dashes and underscores.

### Alpha Num

The field under validation must be entirely alpha-numeric characters.

### Array Type

The field under validation must be of array type.

### Between

The field under validation must have a size between the given min and max. Strings, numerics and arrays are supported.

### Boolean Like

The field under validation must be able to be cast as boolean.
Accepted input are `true`, `false`, `'true'`, `'false'`, `1`, `0`, `'1'`, `'0'`, `'yes'`, `'no'`, `'on'` and `'off'`.

### Boolean Type

The field under validation must be of boolean type.

### Cast To Boolean

Cast the field value to boolean.
Everything that is not `true`, `'true'`, `1`, `'1'`, `'yes'` and `'on'` is converted to `false`.

### Confirmed

The field under validation must have a matching field of `{field}_confirmation`.
For example, if the field under validation is `password`, a matching `password_confirmation` field must be present in the input.

### Date

The field under validation must be a valid, non-relative date according to the `strtotime` PHP function.

### Date After

The field under validation must be a value after a given date.
The dates will be passed into the `strtotime` PHP function in order to be converted to a valid `DateTime` instance.
Instead of passing a date string to be evaluated by `strtotime`, you may specify another field to compare against the date:

```php
use function DonnySim\Validation\field_reference;
use function DonnySim\Validation\rule;

rule('ends')->dateAfter(field_reference('starts'));
```

### Date After Or Equal

The field under validation must be a value after or equal to the given date.
For more information, see the [Date After](#date-after) rule.

### Date Before

The field under validation must be a value preceding the given date.
The dates will be passed into the PHP `strtotime` function in order to be converted into a valid DateTime instance.
In addition, like the [Date After](#date-after) rule, the name of another field under validation may be supplied as the value of `date`.

### Date Before Or Equal

The field under validation must be a value preceding or equal to the given date.
The dates will be passed into the PHP `strtotime` function in order to be converted into a valid `DateTime` instance.
In addition, like the [Date After](#date-after) rule, the name of another field under validation may be supplied as the value of `date`.

### Date Equal

The field under validation must be a value equal to the given date.
The dates will be passed into the PHP `strtotime` function in order to be converted into a valid `DateTime` instance.

### Date Format

The field under validation must match the given format.
This validation rule supports all formats supported by PHP's `DateTime` class.

### Different

The field under validation must have a different value than field.

### Digits

The field under validation must be numeric and must have an exact length of *value*.

### Digits Between

The field under validation must be *numeric* and must have a length between the given *min* and *max*.

### Distinct

When validating arrays, the field under validation must not have any duplicate values:

```php
use function DonnySim\Validation\rule;

rule('foo.*.id')->distinct();
```

### Email

The field under validation must be formatted as an email address.
This validation rule utilizes the `egulias/email-validator` package for validating the email address.
By default, the `RFCValidation` validator is applied

### Ends With

The field under validation must end with one of the given values.

### Filled

The field under validation must not be empty when it is present.

### Greater Than

The field under validation must be greater than the given *field* or *value*.
Strings, numerics and arrays are supported.

### Greater Than Or Equal

The field under validation must be greater than or equal to the given *field* or *value*.
Strings, numerics and arrays are supported.

### In

The field under validation must be included in the given list of values.

### Integer Type

The field under validation must be of integer type.

### Ip Address

The field under validation must be an IP address.

### Json

The field under validation must be a valid JSON string.

### Less Than

The field under validation must be less than the given *field* or *value*.
Strings, numerics and arrays are supported.

### Less Than Or Equal

The field under validation must be less than or equal to the given *field* or *value*.
Strings, numerics and arrays are supported.

### Not In

The field under validation must not be included in the given list of values.

### Not Regex

The field under validation must not match the given regular expression.

Internally, this rule uses the PHP `preg_match` function.
The pattern specified should obey the same formatting required by `preg_match` and thus also include valid delimiters.
For example: `'/^.+$/i'`.

### Nullable

The field under validation may be `null`.

### Numeric

The field under validation must be numeric.

### Numeric Float

The field under validation must be a `float` or string representing float.

### Numeric Integer

The field under validation must be a `int` or string representing integer.

### Omit From Data

The field data under validation will not be extracted to validated data.
This is useful when a field, and it's children are validated but only children data should be included.
For example:

```php
use DonnySim\Validation\Validator;
use function DonnySim\Validation\rule;

$validator = new Validator(
    $messageResolver,
    [
        'roles' => [
            ['id' => 1, 'name' => 'Developer'],
            ['id' => '2', 'name' => 'Admin'],
        ],
    ],
    [
        rule('roles')->omitFromData(),
        rule('roles.*.id')->required()->integerType(),
    ]
);

$data = $validator->getValidatedData();
//[
//    'roles' => [
//        ['id' => 1],
//    ],
//];
```

### Pipe

Sometimes it's necessary to do tweak rules depending on entry value, you can achieve this via `pipe` rule:

```php
use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules;
use function DonnySim\Validation\rule;

rule('roles.*')
    ->arrayType()
    ->pipe(function (EntryPipeline $pipeline, Entry $entry) {
        if (isset($entry->getValue()['temp_id'])) {
            $pipeline->insertNext(fn(Rules $rules) => $rules->rule(new MyCustomRule()));
        } else {
            $pipeline->insertNext(fn(Rules $rules) => $rules->rule(new MyOtherRule()));
        }
    });
    // ->...
```

### Present

The field under validation must be present in the input data but can be empty.

### Regex

The field under validation must match the given regular expression.

Internally, this rule uses the PHP `preg_match` function.
The pattern specified should obey the same formatting required by `preg_match` and thus also include valid delimiters.
For example: `'/^.+$/i'`.

### Required

The field under validation must be present in the input data and not empty.
A field is considered "empty" if one of the following conditions are true:

- The value is `null`.
- The value is an empty string.
- The value is an empty array or empty `Countable` object.
- The value is an uploaded file with no path.

### Rule

Allows adding custom rule.

### Rules

Allows adding multiple custom rules.

### Same

The given field must match the field under validation.

### Set Value If Missing

When the field under validation is missing, set a custom value.
This will stop the validator from proceeding.

### Sometimes

If field under validation is missing, stop validation flow.

### Starts With

The field under validation must start with one of the given values.

### String Type

The field under validation must be of string type.

### Timezone

The field under validation must be a valid timezone identifier according to the `timezone_identifiers_list` PHP function.

### Url

The field under validation must be a valid URL.

### Uuid

The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique identifier (UUID).

### When

This rule allows controlling rule flow based on static value.
For example:

```php
use DonnySim\Validation\Rules;
use function DonnySim\Validation\rule;

rule('foo')
    ->when($isCreating, function (Rules $rules) {
        $rules->required();
    });
```
