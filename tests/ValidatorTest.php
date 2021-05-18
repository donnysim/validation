<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\Contracts\MessageResolver;
use DonnySim\Validation\Exceptions\ValidationException;
use DonnySim\Validation\Process\ValidationMessage;
use DonnySim\Validation\Rules;
use DonnySim\Validation\Tests\Concerns\ValidatorHelpers;
use DonnySim\Validation\Tests\Stubs\ClientRuleGroupStub;
use DonnySim\Validation\Validator;
use PHPUnit\Framework\TestCase;
use function array_map;

final class ValidatorTest extends TestCase
{
    use ValidatorHelpers;

    /**
     * @test
     */
    public function it_throws_validation_exception_on_failure(): void
    {
        $this->expectException(ValidationException::class);

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()]);
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

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()]);
        $v->validate();

        self::assertTrue($usesCustomHandler);

        Validator::setFailureHandler(null);
    }

    /**
     * @test
     */
    public function it_returns_validation_messages(): void
    {
        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()]);
        self::assertTrue($v->fails());

        self::assertCount(1, $v->getValidationMessages());
        self::assertContainsOnlyInstancesOf(ValidationMessage::class, $v->getValidationMessages());
    }

    /**
     * @test
     */
    public function it_returns_resolved_messages(): void
    {
        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()]);
        self::assertTrue($v->fails());

        $messages = $v->getMessages(new class implements MessageResolver {
            public function resolve(array $messages): array
            {
                return array_map(static fn (ValidationMessage $message) => [$message->getKey(), $message->getEntry()->getPath()], $messages);
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
        Validator::setDefaultMessageResolver(new class implements MessageResolver {
            public function resolve(array $messages): array
            {
                return array_map(static fn (ValidationMessage $message) => [$message->getKey(), $message->getEntry()->getPath()], $messages);
            }
        });

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()]);
        self::assertTrue($v->fails());

        $messages = $v->getMessages();

        self::assertCount(1, $messages);
        self::assertSame([['required', 'foo']], $messages);

        Validator::setDefaultMessageResolver(null);
    }

    /**
     * @test
     */
    public function it_omits_values_from_data(): void
    {
        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->omitFromData()]);
        self::assertSame([], $v->getValidatedData());

        $v = $this->makeValidator([
            'foo' => [
                ['id' => 1, 'name' => 'test'],
            ],
        ], [
            Rules::make('foo')->omitFromData()->arrayType(),
            Rules::make('foo.*.name')->required(),
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
        $v = $this->makeValidator([
            'foo' => null,
            'client' => [
                'last_name' => 'test',
            ],
        ], [
            Rules::make('foo')->required(),
            new ClientRuleGroupStub(),
        ]);
        $this->assertValidationFail($v, 'foo', 'foo is required', 2);
        $this->assertValidationFail($v, 'client.name', 'client.name is required', 2);
    }
}
