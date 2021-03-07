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

Referencing other fields uses `reference`, e.g. `rule('start_date')->before(reference('end_date'))`. Support for references depends on rules, and some rules might
accept other field name as an argument e.g. `same('other_field')`. Referenced field values are provided as is from data without any validation passes.

You can use your own rule via `->rule($myRule)`.

The target is to have a validator with no unexpected behavior like a string '1' passes an integer rule. This is build for data integrity and flexibility not for
performance/insanely fast validation.

Each rule is a separate class, it implements either `\DonnySim\Validation\Contracts\SingleRule` or
`\DonnySim\Validation\Contracts\BatchRule`, for examples you can just look up the `Rules` directory.
(there are no `BatchRule` currently).

Translations are provided via `\DonnySim\Validation\Contracts\MessageResolver` contract.

## Laravel

TODO

## Available Validation Rules

Below is a list of all available validation rules and their function:

<!-- table-rule-start -->

<table>
<tbody>
<tr>
<td align="center" valign="top"><strong>A</strong></td>
<td width="1000"><a href="#accepted">Accepted</a></br><a href="#active-url">Active Url</a></br><a href="#alpha">Alpha</a></br><a href="#alpha-dash">Alpha Dash</a></br><a href="#alpha-num">Alpha Num</a></br><a href="#array-type">Array Type</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>B</strong></td>
<td width="1000"><a href="#between">Between</a></br><a href="#boolean-like">Boolean Like</a></br><a href="#boolean-type">Boolean Type</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>C</strong></td>
<td width="1000"><a href="#cast-to-boolean">Cast To Boolean</a></br><a href="#confirmed">Confirmed</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>D</strong></td>
<td width="1000"><a href="#date">Date</a></br><a href="#date-after">Date After</a></br><a href="#date-after-or-equal">Date After Or Equal</a></br><a href="#date-before">Date Before</a></br><a href="#date-before-or-equal">Date Before Or Equal</a></br><a href="#date-equal">Date Equal</a></br><a href="#date-format">Date Format</a></br><a href="#different">Different</a></br><a href="#digits">Digits</a></br><a href="#digits-between">Digits Between</a></br><a href="#distinct">Distinct</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>E</strong></td>
<td width="1000"><a href="#email">Email</a></br><a href="#ends-with">Ends With</a></br><a href="#exists">Exists</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>F</strong></td>
<td width="1000"><a href="#filled">Filled</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>G</strong></td>
<td width="1000"><a href="#greater-than">Greater Than</a></br><a href="#greater-than-or-equal">Greater Than Or Equal</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>I</strong></td>
<td width="1000"><a href="#in">In</a></br><a href="#integer-type">Integer Type</a></br><a href="#ip-address">Ip Address</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>J</strong></td>
<td width="1000"><a href="#json">Json</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>L</strong></td>
<td width="1000"><a href="#less-than">Less Than</a></br><a href="#less-than-or-equal">Less Than Or Equal</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>M</strong></td>
<td width="1000"><a href="#max">Max</a></br><a href="#min">Min</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>N</strong></td>
<td width="1000"><a href="#not-in">Not In</a></br><a href="#not-regex">Not Regex</a></br><a href="#nullable">Nullable</a></br><a href="#numeric">Numeric</a></br><a href="#numeric-float">Numeric Float</a></br><a href="#numeric-integer">Numeric Integer</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>O</strong></td>
<td width="1000"><a href="#omit-from-data">Omit From Data</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>P</strong></td>
<td width="1000"><a href="#pipe">Pipe</a></br><a href="#present">Present</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>R</strong></td>
<td width="1000"><a href="#regex">Regex</a></br><a href="#required">Required</a></br><a href="#rule">Rule</a></br><a href="#rules">Rules</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>S</strong></td>
<td width="1000"><a href="#same">Same</a></br><a href="#set-value-if-missing">Set Value If Missing</a></br><a href="#sometimes">Sometimes</a></br><a href="#starts-with">Starts With</a></br><a href="#string-type">String Type</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>T</strong></td>
<td width="1000"><a href="#timezone">Timezone</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>U</strong></td>
<td width="1000"><a href="#unique">Unique</a></br><a href="#url">Url</a></br><a href="#uuid">Uuid</a></td>
</tr>
<tr>
<td align="center" valign="top"><strong>W</strong></td>
<td width="1000"><a href="#when">When</a></td>
</tr>
</tbody>
</table>

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
use function DonnySim\Validation\reference;
use function DonnySim\Validation\rule;

rule('ends')->dateAfter(freference('starts'));
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

### Exists

The field under validation must exist in a given database table.

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

### Max

Alis to [Greater Than Or Equal](#greater-than-or-equal) rule.

### Min

Alis to [Less Than Or Equal](#less-than-or-equal) rule.

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
    ->through(function (EntryPipeline $pipeline, Entry $entry) {
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

### Unique

The field under validation must not exist within the given database table.

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
