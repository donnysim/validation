# Validator

Proof of concept of my validator idea, kind of based/inspired by Laravel.

## Installation

Just don't, everything is subject to change or this might just be deleted.
I'm unsure about meany things including rule naming, rule array - [rule('email')] vs ['email' => rule()->] etc.

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
        rule('roles', false)->required()->min(1),
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

Referencing other fields uses `field_reference`, e.g. `rule('start_date')->before(field_reference('end_date'))`.
Support for references depends on rules, and some rules might accept other field name as an argument e.g. `same('other_field')`.
Referenced field values are provided as is from data without any validation passes.

You can use your own rule via `->rule($myRule)`.

The target is to have a validator with no unexpected behavior like a string '1' passes an integer rule.
This is build for data integrity and flexibility not for performance/insanely fast validation.

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

## Setting value when missing

You can set the field value to anything you want if it's missing using `setValueIfMissing` rule.
This should be the first rule in validation chain and no further rules will be processed if the entry is missing.

## Omitting rule from data

If you want to omit a rule from final result you can use:

```php
<?php

use function DonnySim\Validation\rule;

rule('roles', false)->required()->min(1);
// OR
rule('roles')->omitFromData()->required()->min(1);
rule('roles.*.id')->required()->integerType();
```

## Dynamic rule flow

Sometimes you could want to do tweak rules depending on entry value, you can achieve this via `pipe` rule:

```php
<?php

use DonnySim\Validation\Entry;
use DonnySim\Validation\EntryPipeline;
use DonnySim\Validation\Rules;
use function DonnySim\Validation\rule;

rule('roles.*')
    ->arrayType()
    ->pipe(function (EntryPipeline $pipeline, Entry $entry) {
        if (isset($entry->getValue()['temp_id'])) {
            $pipeline->insertNext(fn(Rules $rules) => $rules->rule(new CreateRoleRule()));
        } else {
            $pipeline->insertNext(fn(Rules $rules) => $rules->rule(new UpdateRoleRule()));
        }
    })
    ->otherRulesAfterCreateOrUpdateRule();
```

For cases where rules don't depend on entry value you can utilize `when`:

```php
<?php

use DonnySim\Validation\Rules;
use function DonnySim\Validation\rule;

rule('foo')
    ->when($isCreating, static function (Rules $rules) {
        $rules->required();
    });
```

## TODO

- More rules;
- Decide if it's for Laravel or more general validator.

    For laravel we could include service provider, validation message replacements, rules related to laravel like exists etc.
