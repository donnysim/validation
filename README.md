# Validator

The goal of this library is to have a validator with no unexpected behavior like a string '1' passes an integer type rule.
This is built for data integrity and not for performance/insanely fast validation.

## Installation

TODO

## TODO

- [ ] fix keys with dots or asterisks possible problem?
- [ ] rework rules to allow validating without process?

## Validation

```php
<?php

use DonnySim\Validation\Validator;
use DonnySim\Validation\RuleSet;

$validator = new Validator(
    [
        'agreement' => 'yes',
        'email' => 'no-email',
        'roles' => [
            ['id' => 1, 'name' => 'Developer'],
            ['id' => '2', 'name' => 'Admin'],
        ],
    ],
    [
        RuleSet::make('email')->required()->email(),
        RuleSet::make('blocked')->setValueIfNotPresent(false)->booleanType(),
        RuleSet::make('roles')->omit()->required()->arrayType(),
        RuleSet::make('roles.*.id')->required()->integerType(),
        RuleSet::make('agreement')->required()->accepted()->toBoolean(),
    ]
);

$data = $validator->getValidatedData();
// [
//     'agreement' => true,
//     'blocked' => false,
//     'roles' => [
//         ['id' => 1],
//     ],
// ];
```

The validator executes rules in the same order they were provided:

```php
RuleSet::make('email')->email()->required(),
```

This will trigger email validation first and only then check the required field.

### Validator reuse

After first validation trigger the result will be cached, meaning that any changes to the validator like `setData` will be ignored.
To reset the state call the `reset` on the validator.

### Custom rules

Each rule is a separate class, it must implement `\DonnySim\Validation\Interfaces\RuleSetInterface`.
The rules get access to `\DonnySim\Validation\Data\DataEntry` and `\DonnySim\Validation\Process\ValidationProcess` to interact with the process.

If the rule fails, it must call the `fail` method on the process with a failing `\DonnySim\Validation\Message`.
Instead of providing all the required Message parameters one by one you can use the `Message::forEntry` helper.

To stop the validating from proceeding call `$process->stop()`. This will not mark the rule as failed, just prevent further processing
This is useful for changing the value or short-circuiting the validation.

To control whether the value should be extracted you can use `$process->setShouldExtractValue(bool)`.

If rule contains state that needs to be cleaned up between runs,
implement the `\DonnySim\Validation\Interfaces\CleanupStateInterface` interface.
This only runs after the validator finishes the whole validation process.

### Batch value rules

Because of flexibility, batching values for validation would increase the complexity of the validator
beyond the value that it would provide. For such cases you can introduce a cache variable in the rule where you
track necessary values while they are being validated. For example, you can check `\DonnySim\Validation\Rules\Integrity\Distinct`.

In some cases that is not sufficient, for example some rules might be better having all values, like validating database entries,
for this we can combine the rule cache and retrieve all entries using `$process->getAllEntries($entry->getPattern())`.
This will return an array of `DataEntry`. Make sure you check the value before using it as the list will be of unvalidated entries.

### Rule groups

Validator accepts `\DonnySim\Validation\Interfaces\RuleSetGroupInterface` as rules:

```php
use DonnySim\Validation\Validator;
use DonnySim\Validation\RuleSetGroup;
use DonnySim\Validation\RuleSet;

$clientGroup = RuleSetGroup::make([
    RuleSet::make('client.name')->required(),
    RuleSet::make('client.last_name')->required(),
]);

$validator = new Validator(
    [
        'foo' => null,
        'client' => [
            'last_name' => 'test',
        ],
    ],
    [
        RuleSet::make('foo')->required(),
        $clientGroup,
    ]
);
```

This allows you to can create custom rule groups and combine them where necessary.
Combined with [Pipe](#pipe) to tweak the validation flow can lead to powerful and flexible rules groups.

### Rule sets

The RuleSet is not macroable, that is you cannot add new rules dynamically.
This is to encourage creating your own RuleSet instead of relying on magic accessors:

```php

use DonnySim\Validation\Interfaces\RuleSetInterface;
use DonnySim\Validation\Rules\Traits\BaseRulesTrait;
use DonnySim\Validation\Rules\Traits\TypeRulesTrait;

class MyRules implements RuleSetInterface
{
    use BaseRulesTrait;
    use TypeRulesTrait;

    // implement interface methods ... or use DonnySim\Validation\Rules\Traits\RuleSetBaseTrait

    public function exists(string $table, string $column): static
    {
        return $this->rule(new DatabaseExists($table, $column));
    }
}
```

### Exceptions

By default, a `DonnySim\Validation\Exceptions\ValidationException` is thrown on validation failure. You can override validation exceptions using:

```php
use DonnySim\Validation\Validator;

Validator::setFailureHandler(static function (Validator $validator) {
    throw new MyCustomValidationException($validator->resolveMessages());
});
```

### Message and attribute overrides

Validator holds the raw messages from the failed rules that can be accessed via `getMessages`,
but in most cases we want to get a better presentation of the messages to output to the
user - to resolve messages to custom format use `resolveMessages`.

By default, it uses `DonnySim\Validation\ArrayMessageResolver` to resolve messages when calling `$validator->resolveMessages()`,
which returns messages as an array:

```php
[
    'path.attribute' => [
        [
            'key' => 'required', // failing rule name
            'params' => [], // any params provided by failing rule
        ],
    ],
]
```

You can change the resolver at any given time by providing a custom implementation of `DonnySim\Validation\Interfaces\MessageResolverInterface` to the
`resolveMessages`, e.g.:

```php
$validator->resolveMessages(new JsonMessageResolver());
```

You can also override the default message resolver:

```php
use DonnySim\Validation\Validator;

Validator::setMessageResolverFactory(static function () {
    return new MyCustomMessageResolver();
});
```

Now, any time the `resolveMessages` is called `MyCustomMessageResolver` will be used,
but you can still override it for individual cases by providing other resolver as the first argument.

---

To override validation messages and/or attribute names we can provide 3rd and 4th parameter of the validator:

```php
use DonnySim\Validation\Validator;
use DonnySim\Validation\RuleSet;

$validator = new Validator(
    ['foo' => [null, null]],
    [RuleSet::make('foo.*')->required()],
    ['foo.*.required' => ':attribute failed'], // messages
    ['foo.*' => 'foo *', 'foo.0' => 'foo 0'] // attribute name
);
```

By default, it uses `DonnySim\Validation\MessageOverrideProvider`.
To override default override provider:

```php
use DonnySim\Validation\Validator;

Validator::setOverrideProviderFactory(static function (array $messages, array $attributes) {
    return new MyCustomOverrideProvider($messages, $attributes);
});
```

## Rules

<!-- table-rule-start -->

<table>
<tbody>
<tr>
    <td align="center" valign="top"><strong>A</strong></td>
    <td width="1000">
        <a href="#accepted">Accepted</a></br>
        <a href="#alpha">Alpha</a></br>
        <a href="#alpha-dash">Alpha Dash</a></br>
        <a href="#alpha-num">Alpha Num</a></br>
        <a href="#array-type">Array Type</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>B</strong></td>
    <td width="1000">
        <a href="#between">Between</a></br>
        <a href="#boolean-like">Boolean Like</a></br>
        <a href="#boolean-type">Boolean Type</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>C</strong></td>
    <td width="1000">
        <a href="#confirmed">Confirmed</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>D</strong></td>
    <td width="1000">
        <a href="#date">Date</a></br>
        <a href="#date-after">Date After</a></br>
        <a href="#date-after-or-equal">Date After Or Equal</a></br>
        <a href="#date-before">Date Before</a></br>
        <a href="#date-before-or-equal">Date Before Or Equal</a></br>
        <a href="#date-equal">Date Equal</a></br>
        <a href="#date-format">Date Format</a></br>
        <a href="#different">Different</a></br>
        <a href="#digits">Digits</a></br>
        <a href="#distinct">Distinct</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>E</strong></td>
    <td width="1000">
        <a href="#email">Email</a></br>
        <a href="#ends-with">Ends With</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>F</strong></td>
    <td width="1000">
        <a href="#filled">Filled</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>G</strong></td>
    <td width="1000">
        <a href="#greater-than">Greater Than</a></br>
        <a href="#greater-than-or-equal">Greater Than Or Equal</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>I</strong></td>
    <td width="1000">
        <a href="#in">In</a></br>
        <a href="#integer-type">Integer Type</a></br>
        <a href="#ip">Ip</a></br>
        <a href="#ipv4">Ipv4</a></br>
        <a href="#ipv6">Ipv6</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>L</strong></td>
    <td width="1000">
        <a href="#less-than">Less Than</a></br>
        <a href="#less-than-or-equal">Less Than Or Equal</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>M</strong></td>
    <td width="1000">
        <a href="#max">Max</a></br>
        <a href="#min">Min</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>N</strong></td>
    <td width="1000">
        <a href="#not-in">Not In</a></br>
        <a href="#nullable">Nullable</a></br>
        <a href="#numeric">Numeric</a></br>
        <a href="#numeric-float">Numeric Float</a></br>
        <a href="#numeric-integer">Numeric Integer</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>O</strong></td>
    <td width="1000">
        <a href="#optional">Optional</a></br>
        <a href="#omit">Omit</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>P</strong></td>
    <td width="1000">
        <a href="#pipe">Pipe</a></br>
        <a href="#present">Present</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>R</strong></td>
    <td width="1000">
        <a href="#required">Required</a></br>
        <a href="#rule">Rule</a></br>
        <a href="#rules">Rules</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>S</strong></td>
    <td width="1000">
        <a href="#set-value-if-not-present">Set Value If Not Present</a></br>
        <a href="#starts-with">Starts With</a></br>
        <a href="#string-type">String Type</a></br>
    </td>
</tr>
<tr>
    <td align="center" valign="top"><strong>T</strong></td>
    <td width="1000">
        <a href="#to-boolean">To Boolean</a></br>
        <a href="#to-integer">To Integer</a></br>
        <a href="#to-string">To String</a></br>
    </td>
</tr>
</tbody>
</table>

<!-- table-rule-end -->

### Accepted

The field under validation must be `"yes"`, `"on"`, `1`, or `true`.
This is useful for validating "Terms of Service" acceptance or similar fields.

### Alpha

The field under validation must be entirely alphabetic characters.

### Alpha Dash

The field under validation may have alphanumeric characters, as well as dashes and underscores.

### Alpha Num

The field under validation must be entirely alphanumeric characters.

### Array Type

The field under validation must be of array type.

### Between

The field under validation must have a size between the given min and max. Strings, numerics and arrays are supported.

### Boolean Like

The field under validation must be able to be cast as boolean.
Accepted input are `true`, `false`, `'true'`, `'false'`, `1`, `0`, `'1'`, `'0'`, `'yes'`, `'no'`, `'on'` and `'off'`.

### Boolean Type

The field under validation must be of boolean type.

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
use DonnySim\Validation\RuleSet;

RuleSet::make('ends')->dateAfter(RuleSet::ref('starts'));
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

### Distinct

When validating arrays, the field under validation must not have any duplicate values:

```php
use DonnySim\Validation\RuleSet;

RuleSet::make('foo.*.id')->distinct();
```

### Email

The field under validation must be formatted as an email address.
This validation rule utilizes the `egulias/email-validator` package for validating the email address.
By default, the `Email::VALIDATE_RFC, Email::VALIDATE_DNS` validator is applied.

### Ends With

The field under validation must end with one of the given values.

### Greater Than

The field under validation must be greater than the given *field* or *value*.
Strings, numerics and arrays are supported.

### Greater Than Or Equal

The field under validation must be greater than or equal to the given *field* or *value*.
Strings, numerics and arrays are supported.

### In

The field under validation must be included in the given list of values, strict comparison.

### Integer Type

The field under validation must be of integer type.

### Ip

The field under validation must be an IPv4 or IPv6 address.

### Ipv4

The field under validation must be an IPv4 address.

### Ipv6

The field under validation must be an IPv6 address.

### Less Than

The field under validation must be less than the given *field* or *value*.
Strings, numerics and arrays are supported.

### Less Than Or Equal

The field under validation must be less than or equal to the given *field* or *value*.
Strings, numerics and arrays are supported.

### Max

Alis to [Greater Than Or Equal](#greater-than-or-equal) rule.

### Min

Alis to [Less Than Or Equal](#less-than-or-equal) rule.

### Not In

The field under validation must not be included in the given list of values.

### Nullable

The field under validation may be `null`.

### Numeric

The field under validation must be numeric.

### Numeric Float

The field under validation must be a `float` or string representing float.

### Numeric Integer

The field under validation must be a `int` or string representing integer.

### Optional

If field under validation is missing, skip validation flow.

### Omit

The field data under validation will not be extracted to validated data.
This is useful when a field, and it's children are validated but only children data should be included.
For example:

```php
use DonnySim\Validation\Validator;
use DonnySim\Validation\RuleSet;

$validator = new Validator(
    [
        'roles' => [
            ['id' => 1, 'name' => 'Developer'],
            ['id' => '2', 'name' => 'Admin'],
        ],
    ],
    [
        RuleSet::make('roles')->omit(),
        RuleSet::make('roles.*.id')->required()->integerType(),
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

Pass through callback. This allows to inspect, e.g.:

```php
use DonnySim\Validation\RuleSet;

RuleSet::make('roles.*')->arrayType()->pipe('dd');
```

Insert additional rules to be executed right after the pipe, e.g.:

```php
use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Process\ValidationProcess;

RuleSet::make('roles.*')
    ->arrayType()
    ->pipe(function (DataEntry $entry, ValidationProcess $process) {
        if (isset($entry->getValue()['temp_id'])) {
            $process->getCurrent()->insert(RuleSet::make()->rule(CreateCustomRule::class));
        } else {
            $process->getCurrent()->insert(RuleSet::make()->rule(UpdateCustomRule::class));
        }
    })
    // Will be called after CreateCustomRule or UpdateCustomRule
    ->rule(FinalRule::class);
```

or change the rules to be processed, e.g.:

```php
use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Process\ValidationProcess;

RuleSet::make('roles.*')
    ->arrayType()
    ->pipe(function (DataEntry $entry, ValidationProcess $process) {
        if (isset($entry->getValue()['temp_id'])) {
            $process->getCurrent()->replace(RuleSet::make()->rule(CreateCustomRule::class));
        } else {
            $process->getCurrent()->replace(RuleSet::make()->rule(UpdateCustomRule::class));
        }
    })
    // Will never be called, "replace" replaces further rules
    ->rule(FinalRule::class);
```

### Present

The field under validation must be present in the input data but can be empty.

### Required

The field under validation must be present in the input data and not empty.
A field is considered "empty" if one of the following conditions are true:

- The value is `null`.
- The value is an empty string, trimmed before checking.
- The value is an empty array.

### Rule

Allows adding custom rule.

### Rules

Allows adding multiple custom rules.

### Set Value If Not Present

When the field under validation is missing, set a custom value.
This will stop the validator from proceeding.

### Starts With

The field under validation must start with one of the given values.

### String Type

The field under validation must be of string type.

### To Boolean

Cast the value to boolean. It will be converted to `true` if it matches `'true'`, `1`, `'1'`, `'yes'`, `'on'`, otherwise it will be `false`.

### To Integer

Cast the value to integer. It will be cast using PHP cast - `(int)$value`.

### To String

Cast the value to string. The following logic will be used:

- return `'true'` or `'false'` if the value is bool;
- return `'array'` if the value is array;
- return `(string)$value` otherwise;
