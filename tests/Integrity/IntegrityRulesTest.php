<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Integrity;

use DonnySim\Validation\Rules\Integrity\Email\Email;
use DonnySim\Validation\Rules\Integrity\IpAddress;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Tests\Traits\ValidationHelpersTrait;
use PHPUnit\Framework\TestCase;

final class IntegrityRulesTest extends TestCase
{
    use ValidationHelpersTrait;

    /**
     * @test
     */
    public function accepted_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [RuleSet::make('foo')->accepted()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be accepted']]);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->accepted()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be accepted']]);

        $v = $this->makeValidator([], [RuleSet::make('foo')->accepted()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be accepted']]);

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->accepted()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be accepted']]);

        $v = $this->makeValidator(['foo' => false], [RuleSet::make('foo')->accepted()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be accepted']]);

        $v = $this->makeValidator(['foo' => 'false'], [RuleSet::make('foo')->accepted()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be accepted']]);

        $v = $this->makeValidator(['foo' => 'yes'], [RuleSet::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'on'], [RuleSet::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [RuleSet::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => true], [RuleSet::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [RuleSet::make('foo')->accepted()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function confirmed(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->confirmed()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo')->confirmed()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be confirmed']]);

        $v = $this->makeValidator(['foo' => 'bar', 'foo_confirmation' => 'baz'], [RuleSet::make('foo')->confirmed()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be confirmed']]);

        $v = $this->makeValidator(['foo' => 'bar', 'foo_confirmation' => 'bar'], [RuleSet::make('foo')->confirmed()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1e2', 'foo_confirmation' => '100'], [RuleSet::make('foo')->confirmed()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be confirmed']]);

        $v = $this->makeValidator(['foo' => [['name' => '1e2']]], [RuleSet::make('foo.*.name')->confirmed()]);
        $this->assertValidationFail($v, ['foo.0.name' => ['foo.0.name must be confirmed']]);
    }

    /**
     * @test
     */
    public function distinct_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['foo', 'foo']], [RuleSet::make('foo.*')->distinct()]);
        $this->assertValidationFail($v, [
            'foo.1' => ['foo.1 must be distinct'],
        ]);

        $v = $this->makeValidator(['foo' => ['foo', 'bar']], [RuleSet::make('foo.*')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 1]]], [RuleSet::make('foo.*.id')->distinct()]);
        $this->assertValidationFail($v, ['foo.baz.id' => ['foo.baz.id must be distinct']]);

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 'qux'], 'baz' => ['id' => 'QUX']]], [RuleSet::make('foo.*')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 2]]], [RuleSet::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1, 'nested' => ['id' => 1]]]], [RuleSet::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => 2]]], [RuleSet::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 2]]]]], [RuleSet::make('cat.*.prod.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['cat' => ['sub' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 2]]]]]], [RuleSet::make('cat.sub.*.prod.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['foo' => ['foo', 'bar'], 'bar' => ['foo', 'bar']],
            [RuleSet::make('foo.*')->distinct(), RuleSet::make('bar.*')->distinct()]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => 1]]], [RuleSet::make('foo.*.id')->distinct()]);
        $this->assertValidationFail($v, ['foo.1.id' => ['foo.1.id must be distinct']]);

        $v = $this->makeValidator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 1]]]]], [RuleSet::make('cat.*.prod.*.id')->distinct()]);
        $this->assertValidationFail($v, ['cat.1.prod.0.id' => ['cat.1.prod.0.id must be distinct']]);

        $v = $this->makeValidator(['cat' => ['sub' => [['prod' => [['id' => 2]]], ['prod' => [['id' => 2]]]]]], [RuleSet::make('cat.sub.*.prod.*.id')->distinct()]);
        $this->assertValidationFail($v, ['cat.sub.1.prod.0.id' => ['cat.sub.1.prod.0.id must be distinct']]);

        $v = $this->makeValidator(
            ['foo' => ['foo', 'foo'], 'bar' => ['bar', 'baz']],
            [RuleSet::make('foo.*')->distinct(), RuleSet::make('bar.*')->distinct()]
        );
        $this->assertValidationFail($v, ['foo.1' => ['foo.1 must be distinct']]);

        $v = $this->makeValidator(
            ['foo' => ['foo', 'foo'], 'bar' => ['bar', 'bar']],
            [RuleSet::make('foo.*')->distinct(), RuleSet::make('bar.*')->distinct()]
        );
        $this->assertValidationFail($v, [
            'foo.1' => ['foo.1 must be distinct'],
            'bar.1' => ['bar.1 must be distinct'],
        ]);
    }

    /**
     * @test
     */
    public function different_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'boom'], [RuleSet::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => null], [RuleSet::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'bar'], [RuleSet::make('foo')->different('baz')]);
        $this->assertValidationFail($v, ['foo' => ['foo must be different from baz']]);

        $v = $this->makeValidator(['foo' => '1e2', 'baz' => '100'], [RuleSet::make('foo')->different('baz')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function digits_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->digits(1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [RuleSet::make('foo')->digits(5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345], [RuleSet::make('foo')->digits(5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345.0], [RuleSet::make('foo')->digits(5)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have 5 digits']]);

        $v = $this->makeValidator(['foo' => '123'], [RuleSet::make('foo')->digits(200)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have 200 digits']]);

        $v = $this->makeValidator(['foo' => '+2.37'], [RuleSet::make('foo')->digits(200)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have 200 digits']]);

        $v = $this->makeValidator(['foo' => '2e7'], [RuleSet::make('foo')->digits(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have 3 digits']]);
    }

    /**
     * @test
     */
    public function digits_between_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->digitsBetween(1, 2)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [RuleSet::make('foo')->digitsBetween(1, 6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345], [RuleSet::make('foo')->digitsBetween(1, 6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345.0], [RuleSet::make('foo')->digitsBetween(1, 6)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have digits between 1 and 6']]);

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo')->digitsBetween(1, 10)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have digits between 1 and 10']]);

        $v = $this->makeValidator(['foo' => '123'], [RuleSet::make('foo')->digitsBetween(4, 5)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have digits between 4 and 5']]);

        $v = $this->makeValidator(['foo' => '+12.3'], [RuleSet::make('foo')->digitsBetween(1, 6)]);
        $this->assertValidationFail($v, ['foo' => ['foo must have digits between 1 and 6']]);
    }

    /**
     * @test
     */
    public function email_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('email')->email()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'aslsdlks'], [RuleSet::make('email')->email()]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);

        $v = $this->makeValidator(['email' => ['aslsdlks']], [RuleSet::make('email')->email()]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);

        $v = $this->makeValidator(['email' => 'foo@gmail.com'], [RuleSet::make('email')->email()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'foo@gmäil.com'], [RuleSet::make('email')->email()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'foo@bar '], [RuleSet::make('email')->email([Email::VALIDATE_STRICT])]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);

        $v = $this->makeValidator(['email' => 'foo@bar'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER])]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);

        $v = $this->makeValidator(['email' => 'example@example.com'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'exämple@example.com'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER])]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);

        $v = $this->makeValidator(['email' => 'exämple@exämple.com'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER])]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);

        $v = $this->makeValidator(['email' => 'foo@bar'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER_UNICODE])]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);

        $v = $this->makeValidator(['email' => 'example@example.com'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER_UNICODE])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'exämple@example.com'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER_UNICODE])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['email' => 'exämple@exämple.com'], [RuleSet::make('email')->email([Email::VALIDATE_FILTER_UNICODE])]);
        self::assertFalse($v->passes());
        $this->assertValidationFail($v, ['email' => ['email must be email']]);
    }

    /**
     * @test
     */
    public function in_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'foo'], [RuleSet::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, ['foo' => ['foo must be in array']]);

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, ['foo' => ['foo must be in array']]);

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo')->in(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar', 'baz']], [RuleSet::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, ['foo' => ['foo must be in array']]);
    }

    /**
     * @test
     */
    public function not_in_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'foo'], [RuleSet::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [RuleSet::make('foo')->notIn(['bar', 'baz'])]);
        $this->assertValidationFail($v, ['foo' => ['foo must not be in array']]);

        $v = $this->makeValidator(['foo' => ['bar', 'baz']], [RuleSet::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function ends_with_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->endsWith('hello')]);
        $this->assertValidationFail($v, ['foo' => ['foo must end with hello']]);

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->endsWith('world')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->endsWith(['world', 'hello'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->endsWith('http')]);
        $this->assertValidationFail($v, ['foo' => ['foo must end with http']]);

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->endsWith(['https', 'http'])]);
        $this->assertValidationFail($v, ['foo' => ['foo must end with https, http']]);
    }

    /**
     * @test
     */
    public function starts_with_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->startsWith('hello')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->startsWith('world')]);
        $this->assertValidationFail($v, ['foo' => ['foo must start with world']]);

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->startsWith(['world', 'hello'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->startsWith('http')]);
        $this->assertValidationFail($v, ['foo' => ['foo must start with http']]);

        $v = $this->makeValidator(['foo' => 'hello world'], [RuleSet::make('foo')->startsWith(['https', 'http'])]);
        $this->assertValidationFail($v, ['foo' => ['foo must start with https, http']]);
    }

    /**
     * @test
     */
    public function ip_address_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->in(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'aslsdlks'], [RuleSet::make('foo')->ip()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be a valid ip address']]);

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [RuleSet::make('foo')->ip()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [RuleSet::make('foo')->ip(IpAddress::TYPE_IPV4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [RuleSet::make('foo')->ipv4()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '::1'], [RuleSet::make('foo')->ip(IpAddress::TYPE_IPV6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '::1'], [RuleSet::make('foo')->ipv6()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [RuleSet::make('foo')->ip(IpAddress::TYPE_IPV6)]);
        $this->assertValidationFail($v, ['foo' => ['foo must be a valid ipv6 address']]);

        $v = $this->makeValidator(['foo' => '127.0.0.1'], [RuleSet::make('foo')->ipv6()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be a valid ipv6 address']]);

        $v = $this->makeValidator(['foo' => '::1'], [RuleSet::make('foo')->ip(IpAddress::TYPE_IPV4)]);
        $this->assertValidationFail($v, ['foo' => ['foo must be a valid ipv4 address']]);

        $v = $this->makeValidator(['foo' => '::1'], [RuleSet::make('foo')->ipv4()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be a valid ipv4 address']]);
    }
}
