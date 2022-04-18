<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Exceptions\ValidationException;
use DonnySim\Validation\Interfaces\MessageOverrideProviderInterface;
use DonnySim\Validation\Interfaces\MessageResolverInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\Process\ValidationProcess;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\RuleSetGroup;
use DonnySim\Validation\Tests\Stubs\NestedRule;
use DonnySim\Validation\Tests\Traits\ValidationHelpersTrait;
use DonnySim\Validation\Validator;
use PHPUnit\Framework\TestCase;
use function array_map;

final class ValidatorTest extends TestCase
{
    use ValidationHelpersTrait;

    /**
     * @test
     */
    public function it_throws_validation_exception_on_failure(): void
    {
        $this->expectException(ValidationException::class);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->required()]);
        $v->validate();
    }

    /**
     * @test
     */
    public function it_uses_custom_failure_handler(): void
    {
        $usesCustomHandler = false;

        Validator::setFailureHandler(static function (Validator $validator) use (&$usesCustomHandler) {
            $usesCustomHandler = true;
        });

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->required()]);
        $v->validate();

        self::assertTrue($usesCustomHandler);

        Validator::setFailureHandler(null);
    }

    /**
     * @test
     */
    public function it_returns_validation_messages(): void
    {
        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->required()]);
        self::assertTrue($v->fails());

        self::assertCount(1, $v->getMessages());
        self::assertContainsOnlyInstancesOf(Message::class, $v->getMessages());
    }

    /**
     * @test
     */
    public function it_returns_resolved_messages(): void
    {
        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->required()]);
        self::assertTrue($v->fails());

        $messages = $v->resolveMessages(new class() implements MessageResolverInterface {
            public function resolveMessages(array $messages, MessageOverrideProviderInterface $overrideProvider): array
            {
                return array_map(static fn (Message $message) => [$message->getFailingRuleName(), $message->getPath()], $messages);
            }
        });

        self::assertCount(1, $messages);
        self::assertSame([['required', 'foo']], $messages);
    }

    /**
     * @test
     */
    public function it_uses_default_message_resolver(): void
    {
        Validator::setMessageResolverFactory(static function () {
            return new class() implements MessageResolverInterface {
                public function resolveMessages(array $messages, MessageOverrideProviderInterface $overrideProvider): array
                {
                    return ['custom message'];
                }
            };
        });

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->required()]);

        self::assertSame(['custom message'], $v->resolveMessages());

        Validator::setMessageResolverFactory(null);
    }

    /**
     * @test
     */
    public function it_uses_default_override_provider(): void
    {
        Validator::setOverrideProviderFactory(static function () {
            return new class() implements MessageOverrideProviderInterface {
                public function getMessageOverride(Message $message): ?string
                {
                    return 'test :attribute';
                }

                public function getAttributeOverride(Message $message): string
                {
                    return 'test';
                }
            };
        });

        $v = $this->makeValidator(
            ['foo' => [null, null]],
            [RuleSet::make('foo.*')->required()],
            ['foo.*.required' => ':attribute failed'],
            ['foo.*' => 'foo_*', 'foo.0' => 'foo_0']
        );
        $this->assertValidationFail($v, ['foo.0' => ['test test'], 'foo.1' => ['test test']]);

        Validator::setOverrideProviderFactory(null);
    }

    /**
     * @test
     */
    public function it_returns_only_validated_data(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')]);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => 'bar'], []);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('baz')]);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo')]);
        self::assertSame(['foo' => 'bar'], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo.*')]);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => ['bar', 'baz']], [RuleSet::make('foo.*')]);
        self::assertSame(['foo' => ['bar', 'baz']], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => [['name' => 'bar'], ['name' => 'baz']]], [RuleSet::make('foo.*')]);
        self::assertSame(['foo' => [['name' => 'bar'], ['name' => 'baz']]], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => [['name' => 'bar'], ['name' => 'baz']]], [RuleSet::make('foo.*.bar')]);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => [['name' => 'bar', 'first' => true], ['name' => 'baz', 'last' => true]]], [RuleSet::make('foo.*.name')]);
        self::assertSame(['foo' => [['name' => 'bar'], ['name' => 'baz']]], $v->getValidatedData());

        $v = $this->makeValidator(['foo' => [['name' => 'bar', 'first' => true], ['name' => 'baz', 'last' => true]]], [
            RuleSet::make('foo.*.name'),
            RuleSet::make('foo.*.first'),
        ]);
        self::assertSame(['foo' => [['name' => 'bar', 'first' => true], ['name' => 'baz']]], $v->getValidatedData());
    }

    /**
     * @test
     */
    public function it_omits_values_from_data(): void
    {
        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo')->omit()]);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator([
            'foo' => [
                ['id' => 1, 'name' => 'test'],
            ],
        ], [
            RuleSet::make('foo')->omit()->arrayType(),
            RuleSet::make('foo.*.name')->required(),
        ]);
        self::assertSame([
            'foo' => [['name' => 'test']],
        ], $v->getValidatedData());
    }

    /**
     * @test
     */
    public function it_accepts_rule_groups(): void
    {
        $group = RuleSetGroup::make([
            RuleSet::make('client.name')->required(),
            RuleSet::make('client.last_name')->required(),
        ]);

        $v = $this->makeValidator([
            'foo' => null,
            'client' => [
                'last_name' => 'test',
            ],
        ], [
            RuleSet::make('foo')->required(),
            $group,
        ]);
        $this->assertValidationFail($v, ['foo' => ['foo is required'], 'client.name' => ['client.name is required']]);
    }

    /**
     * @test
     */
    public function it_overrides_message(): void
    {
        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->required()], ['foo.required' => 'failed test']);
        $this->assertValidationFail($v, ['foo' => ['failed test']]);

        $v = $this->makeValidator(['foo' => [null, null]], [RuleSet::make('foo.*')->required()], ['foo.1.required' => 'failed 1', 'foo.*.required' => 'failed *']);
        $this->assertValidationFail($v, ['foo.0' => ['failed *'], 'foo.1' => ['failed 1']]);
    }

    /**
     * @test
     */
    public function it_overrides_attribute_names(): void
    {
        $v = $this->makeValidator(
            ['foo' => null],
            [RuleSet::make('foo')->required()],
            ['foo.required' => ':attribute failed'],
            ['foo' => 'custom']
        );
        $this->assertValidationFail($v, ['foo' => ['custom failed']]);

        $v = $this->makeValidator(
            ['foo' => [null, null]],
            [RuleSet::make('foo.*')->required()],
            ['foo.*.required' => ':attribute failed'],
            ['foo.*' => 'foo_*', 'foo.0' => 'foo_0']
        );
        $this->assertValidationFail($v, ['foo.0' => ['foo_0 failed'], 'foo.1' => ['foo_* failed']]);
    }

    /**
     * @test
     */
    public function it_cleanups_stateful_rules(): void
    {
        $v = $this->makeValidator(['foo' => ['foo', 'foo']], [RuleSet::make('foo.*')->distinct()]);
        $this->assertValidationFail($v, [
            'foo.1' => ['foo.1 must be distinct'],
        ]);

        $v->reset();
        $this->assertValidationFail($v, [
            'foo.1' => ['foo.1 must be distinct'],
        ]);
    }

    /**
     * @test
     */
    public function it_inserts_rules_to_be_validated(): void
    {
        $v = $this->makeValidator(['foo' => [true, false]], [
            RuleSet::make('foo.*')
                ->pipe(static function (DataEntry $entry, ValidationProcess $process) {
                    if ($entry->getValue() === true) {
                        $process->getCurrent()->insert(RuleSet::make()->toInteger()->toString());
                    } elseif ($entry->getValue() === false) {
                        $process->getCurrent()->insert(RuleSet::make()->toString());
                    }
                })
                ->pipe(static function (DataEntry $entry, ValidationProcess $process) {
                    $entry->setValue($entry->getValue() . ' value');
                }),
        ]);

        self::assertSame(['foo' => ['1 value', 'false value']], $v->getValidatedData());
    }

    /**
     * @test
     */
    public function it_forks_process_with_different_rules(): void
    {
        $v = $this->makeValidator(['foo' => [5, -5]], [
            RuleSet::make('foo.*')
                ->pipe(static function (DataEntry $entry, ValidationProcess $process): void {
                    if ($entry->getValue() > 0) {
                        $process->getCurrent()->fork(RuleSet::make()->integerType());
                    } else {
                        $process->getCurrent()->fork(RuleSet::make()->booleanType());
                    }
                })
                // Should never be called
                ->stringType(),
        ]);
        $this->assertValidationFail($v, ['foo.1' => ['foo.1 must be boolean']]);
    }

    /**
     * @test
     */
    public function it_ignores_rules_containing_failed_segments(): void
    {
        $v = $this->makeValidator(
            [
                'foo' => [1, 2, 3],
            ],
            [
                RuleSet::make('foo')->arrayType()->max(1),
                RuleSet::make('foo.*')->booleanType(), // must be ignored because `foo` failed
            ]
        );
        $this->assertValidationFail($v, [
            'foo' => ['foo should contain less than 1 items'],
        ]);
    }

    /**
     * @test
     */
    public function it_ignores_rules_containing_failed_segments_added_via_custom_message(): void
    {
        $v = $this->makeValidator(
            [
                'foo' => [1, 2, 3],
                'bar' => 1,
            ],
            [
                RuleSet::make('foo')->arrayType()->pipe(static function (DataEntry $entry, ValidationProcess $process) {
                    $process->getCurrent()->fail(new Message('bar', 'bar', 'bar', 'none'));
                }),
                RuleSet::make('bar')->booleanType(), // must be ignored because `foo` failed
            ]
        );
        $this->assertValidationFail($v, [
            'bar' => ['bar placeholder'],
        ]);
    }

    /**
     * @test
     */
    public function it_allows_adding_rule_sets_during_process(): void
    {
        $v = $this->makeValidator(['foo' => 1, 'bar' => 2], [
            RuleSet::make('foo')
                ->integerType()
                ->pipe(static function (DataEntry $entry, ValidationProcess $process): void {
                    $process->addRuleSet(RuleSet::make('bar')->booleanType());
                }),
        ]);
        $this->assertValidationFail($v, ['bar' => ['bar must be boolean']]);
    }

    /**
     * @test
     */
    public function it_allows_adding_rule_set_groups_during_process(): void
    {
        $v = $this->makeValidator(['foo' => 1, 'bar' => 2], [
            RuleSet::make('foo')
                ->integerType()
                ->pipe(static function (DataEntry $entry, ValidationProcess $process): void {
                    $process->addRuleSet(RuleSetGroup::make([
                        RuleSet::make('bar')->booleanType(),
                    ]));
                }),
        ]);
        $this->assertValidationFail($v, ['bar' => ['bar must be boolean']]);
    }

    /**
     * @test
     */
    public function it_proxies_results(): void
    {
        $v = $this->makeValidator(['foo' => [['name' => 'test'], []]], [RuleSet::make('foo.*')->rule(new NestedRule())], [
            'foo.1.NESTED_RULE' => 'nested rule failed',
        ]);

        $this->assertValidationFail($v, ['foo.1' => ['nested rule failed'], 'foo.1.name' => ['name is required']]);
        self::assertSame(['foo' => [['name' => 'test']]], $v->getValidatedData());
    }
}
