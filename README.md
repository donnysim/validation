# Validator

TODO

The goal of this library is to have a validator with no unexpected behavior like a string '1' passes an integer rule.
This is built for data integrity and not for performance/insanely fast validation.

## Installation

TODO

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

TODO Notes:

The validator executes rules in the same order they were provided.

---

Each rule is a separate class, it must implement `\DonnySim\Validation\Interfaces\RuleSetInterface`.

---

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

---

Note about "batch" rules.

---

By default, a `DonnySim\Validation\Exceptions\ValidationException` is thrown on validation failure. You can override validation exceptions using:

```php
use DonnySim\Validation\Validator;

Validator::setFailureHandler(static function (Validator $validator) {
    throw new MyCustomValidationException($validator->resolveMessages());
});
```

---

By default, it uses `DonnySim\Validation\ArrayMessageResolver` to resolve messaged when calling `$validator->resolveMessages()`,
which resolved messages as an array:

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

Validator::setDefaultMessageResolver(new MyCustomMessageResolver());
```

Now, any time the `resolveMessages` is called `MyCustomMessageResolver` will be used,
but you can still override it for individual cases by providing other resolver as the first argument.

---

To override validation messages and/or attribute names:

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

TODO - make override provider configurable

---

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

---

TODO rules list
