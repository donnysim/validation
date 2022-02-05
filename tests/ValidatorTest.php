<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\Exceptions\ValidationException;
use DonnySim\Validation\Interfaces\MessageResolverInterface;
use DonnySim\Validation\Message;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\RuleSetGroup;
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
            public function resolveMessage(array $messages): array
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
}
