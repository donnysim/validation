<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use Carbon\Carbon;
use DateTime;
use DonnySim\Validation\Rules;
use DonnySim\Validation\Tests\Concerns\ValidatorHelpers;
use PHPUnit\Framework\TestCase;
use stdClass;
use function date;
use function date_default_timezone_set;
use function ini_set;

final class ComparisonRulesTest extends TestCase
{
    use ValidatorHelpers;

    /**
     * @test
     */
    public function between_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => new stdClass()], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => 'asdad'], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => 'anc'], [Rules::make('foo')->between(3, 4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ancfs'], [Rules::make('foo')->between(3, 5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [Rules::make('foo')->between(3, 4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 4 chars');

        $v = $this->makeValidator(['foo' => '4'], [Rules::make('foo')->between(3, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 5 chars');

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->between(3, 5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [Rules::make('foo')->between(3, 4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4, 5, 6]], [Rules::make('foo')->between(3, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 5 items');

        $v = $this->makeValidator(['foo' => 3], [Rules::make('foo')->between(3, 4)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 6], [Rules::make('foo')->between(3, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 5');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->between(3.1, '3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->between('3.1', 3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->between('3.0', '3.5')]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 3.5');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->between(4, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 4 and 5');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->between(4.1, 5.1)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 4.1 and 5.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->between('4.1', '5.1')]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 4.1 and 5.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->between('4.1', '4.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->between('3.1', '4.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.5], [Rules::make('foo')->between(3.4, 3.6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.5], [Rules::make('foo')->between(3.0, 3.4)]);
        $this->assertValidationFail($v, 'foo', 'foo must be between 3 and 3.4');
    }

    /**
     * @test
     */
    public function different_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'boom'], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => null], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'bar'], [Rules::make('foo')->different('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo must be different from baz');

        $v = $this->makeValidator(['foo' => '1e2', 'baz' => '100'], [Rules::make('foo')->different('baz')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function distinct_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['foo', 'foo']], [Rules::make('foo.*')->distinct()]);
        $this->assertValidationFail($v, 'foo.0', 'foo.0 contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 contains duplicate value', 2);

        $v = $this->makeValidator(['foo' => ['foo', 'bar']], [Rules::make('foo.*')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 1]]], [Rules::make('foo.*.id')->distinct()]);
        $this->assertValidationFail($v, 'foo.bar.id', 'foo.bar.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.baz.id', 'foo.baz.id contains duplicate value', 2);

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 'qux'], 'baz' => ['id' => 'QUX']]], [Rules::make('foo.*')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => ['id' => 1], 'baz' => ['id' => 2]]], [Rules::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1, 'nested' => ['id' => 1]]]], [Rules::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => 2]]], [Rules::make('foo.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 2]]]]], [Rules::make('cat.*.prod.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['cat' => ['sub' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 2]]]]]], [Rules::make('cat.sub.*.prod.*.id')->distinct()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['foo' => ['foo', 'bar'], 'bar' => ['foo', 'bar']],
            [Rules::make('foo.*')->distinct(), Rules::make('bar.*')->distinct()]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => 1]]], [Rules::make('foo.*.id')->distinct()]);
        $this->assertValidationFail($v, 'foo.0.id', 'foo.0.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.1.id', 'foo.1.id contains duplicate value', 2);

        $v = $this->makeValidator(['cat' => [['prod' => [['id' => 1]]], ['prod' => [['id' => 1]]]]], [Rules::make('cat.*.prod.*.id')->distinct()]);
        $this->assertValidationFail($v, 'cat.0.prod.0.id', 'cat.0.prod.0.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'cat.1.prod.0.id', 'cat.1.prod.0.id contains duplicate value', 2);

        $v = $this->makeValidator(['cat' => ['sub' => [['prod' => [['id' => 2]]], ['prod' => [['id' => 2]]]]]], [Rules::make('cat.sub.*.prod.*.id')->distinct()]);
        $this->assertValidationFail($v, 'cat.sub.0.prod.0.id', 'cat.sub.0.prod.0.id contains duplicate value', 2);
        $this->assertValidationFail($v, 'cat.sub.1.prod.0.id', 'cat.sub.1.prod.0.id contains duplicate value', 2);

        $v = $this->makeValidator(
            ['foo' => ['foo', 'foo'], 'bar' => ['bar', 'baz']],
            [Rules::make('foo.*')->distinct(), Rules::make('bar.*')->distinct()]
        );
        $this->assertValidationFail($v, 'foo.0', 'foo.0 contains duplicate value', 2);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 contains duplicate value', 2);

        $v = $this->makeValidator(
            ['foo' => ['foo', 'foo'], 'bar' => ['bar', 'bar']],
            [Rules::make('foo.*')->distinct(), Rules::make('bar.*')->distinct()]
        );
        $this->assertValidationFail($v, 'foo.0', 'foo.0 contains duplicate value', 4);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 contains duplicate value', 4);
        $this->assertValidationFail($v, 'bar.0', 'bar.0 contains duplicate value', 4);
        $this->assertValidationFail($v, 'bar.1', 'bar.1 contains duplicate value', 4);
    }

    /**
     * @test
     */
    public function digits_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->digits(1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [Rules::make('foo')->digits(5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345], [Rules::make('foo')->digits(5)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345.0], [Rules::make('foo')->digits(5)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 5 digits');

        $v = $this->makeValidator(['foo' => '123'], [Rules::make('foo')->digits(200)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 200 digits');

        $v = $this->makeValidator(['foo' => '+2.37'], [Rules::make('foo')->digits(200)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 200 digits');

        $v = $this->makeValidator(['foo' => '2e7'], [Rules::make('foo')->digits(3)]);
        $this->assertValidationFail($v, 'foo', 'foo must have 3 digits');
    }

    /**
     * @test
     */
    public function digits_between_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->digitsBetween(1, 2)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12345'], [Rules::make('foo')->digitsBetween(1, 6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345], [Rules::make('foo')->digitsBetween(1, 6)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12345.0], [Rules::make('foo')->digitsBetween(1, 6)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 1 and 6');

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->digitsBetween(1, 10)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 1 and 10');

        $v = $this->makeValidator(['foo' => '123'], [Rules::make('foo')->digitsBetween(4, 5)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 4 and 5');

        $v = $this->makeValidator(['foo' => '+12.3'], [Rules::make('foo')->digitsBetween(1, 6)]);
        $this->assertValidationFail($v, 'foo', 'foo must have digits between 1 and 6');
    }

    /**
     * @test
     */
    public function ends_with_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith('hello')]);
        $this->assertValidationFail($v, 'foo', 'foo must end with hello');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith('world')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith(['world', 'hello'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith('http')]);
        $this->assertValidationFail($v, 'foo', 'foo must end with http');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->endsWith(['https', 'http'])]);
        $this->assertValidationFail($v, 'foo', 'foo must end with https, http');
    }

    /**
     * @test
     */
    public function starts_with_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith('hello')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith('world')]);
        $this->assertValidationFail($v, 'foo', 'foo must start with world');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith(['world', 'hello'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith('http')]);
        $this->assertValidationFail($v, 'foo', 'foo must start with http');

        $v = $this->makeValidator(['foo' => 'hello world'], [Rules::make('foo')->startsWith(['https', 'http'])]);
        $this->assertValidationFail($v, 'foo', 'foo must start with https, http');
    }

    /**
     * @test
     */
    public function less_than_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => new stdClass()], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'aslksd'], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'anc'], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => '211'], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => '22'], [Rules::make('foo')->lessThan(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain less than 3 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThan(3.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3.1');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThan('3.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3.1');

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->lessThan('3.0')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThan(2)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThan(2.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThan('2.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->lessThan('4.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 4.1');
    }

    /**
     * @test
     */
    public function less_than_or_equal_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => new stdClass()], [Rules::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'aslksd'], [Rules::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 3 length');

        $v = $this->makeValidator(['foo' => 'anc'], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '211'], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '22'], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [Rules::make('foo')->lessThanOrEqual(2)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain less than 2 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThanOrEqual(3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->lessThanOrEqual('3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->lessThanOrEqual('3.0')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThanOrEqual(2)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThanOrEqual(2.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->lessThanOrEqual('2.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be less than 2.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->lessThanOrEqual('4.1')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function greater_than_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => new stdClass()], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => 3], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => 2], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => 'abc'], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => 'ab'], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [Rules::make('foo')->greaterThan(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2]], [Rules::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain more than 3 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThan(3.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3.1');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThan('3.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3.1');

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->greaterThan('3.0')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThan(4)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThan(4.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThan('4.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '4.2'], [Rules::make('foo')->numeric()->greaterThan('4.1')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function greater_than_or_equal_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => new stdClass()], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => 3], [Rules::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => 'abc'], [Rules::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ab'], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3 length');

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [Rules::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2]], [Rules::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, 'foo', 'foo should contain more than 3 items');

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThanOrEqual(3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [Rules::make('foo')->greaterThanOrEqual('3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [Rules::make('foo')->greaterThanOrEqual('3.0')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 3');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThanOrEqual(4)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThanOrEqual(4.1)]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '3'], [Rules::make('foo')->numeric()->greaterThanOrEqual('4.1')]);
        $this->assertValidationFail($v, 'foo', 'foo should be greater than 4.1');

        $v = $this->makeValidator(['foo' => '4.1'], [Rules::make('foo')->numeric()->greaterThanOrEqual('4.1')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function same_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'boom'], [Rules::make('foo')->same('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->same('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');

        $v = $this->makeValidator(['foo' => 'bar', 'baz' => 'bar'], [Rules::make('foo')->same('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1e2', 'baz' => '100'], [Rules::make('foo')->same('baz')]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');

        $v = $this->makeValidator(['foo' => null, 'baz' => null], [Rules::make('foo')->same('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['boom'], 'baz' => ['boom']], [Rules::make('foo.*')->same('baz.*')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['boom'], 'baz' => 'boom'], [Rules::make('foo.*')->same('baz')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['foo' => true, 'baz' => 'on'], [
            Rules::make('foo')->same('baz'),
            Rules::make('baz')->castToBoolean(),
        ]);
        $this->assertValidationFail($v, 'foo', 'foo and baz must match');
    }

    /**
     * @test
     */
    public function regex_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'asdasdf'], [Rules::make('foo')->regex('/^[a-z]+$/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'aasd234fsd1'], [Rules::make('foo')->regex('/^[a-z]+$/i')]);
        $this->assertValidationFail($v, 'foo', 'foo must match regex');

        $v = $this->makeValidator(['foo' => 'a,b'], [Rules::make('foo')->regex('/^a,b$/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '12'], [Rules::make('foo')->regex('/^12$/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 12], [Rules::make('foo')->regex('/^12$/i')]);
        $this->assertValidationFail($v, 'foo', 'foo must match regex');
    }

    /**
     * @test
     */
    public function not_regex_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'foo bar'], [Rules::make('foo')->notRegex('/[xyz]/i')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'foo xxx bar'], [Rules::make('foo')->notRegex('/[xyz]/i')]);
        $this->assertValidationFail($v, 'foo', 'foo must not match regex');

        $v = $this->makeValidator(['foo' => 'foo bar'], [Rules::make('foo')->notRegex('/x{3,}/i')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function date_before_and_after_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateBefore('2000-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be before 2000-01-01');

        $v = $this->makeValidator(['x' => new Carbon('2000-01-01')], [Rules::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => [new Carbon('2000-01-01')]], [Rules::make('x')->dateBefore('2012-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be before 2012-01-01');

        $v = $this->makeValidator(['x' => '2012-01-01'], [Rules::make('x')->dateAfter('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => ['2012-01-01']], [Rules::make('x')->dateAfter('2000-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be after 2000-01-01');

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateAfter('2000-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be after 2000-01-01');

        $v = $this->makeValidator(['x' => new Carbon('2012-01-01')], [Rules::make('x')->dateAfter('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2013-01-01',
            ],
            [
                Rules::make('start')->dateAfter('2000-01-01'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2000-01-01',
            ],
            [
                Rules::make('start')->dateAfter('2000-01-01'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 2012-01-01');

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2013-01-01',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => '2000-01-01',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());

        $messages = $v->getMessages($this->makeValidationMessageResolver());
        self::assertSame('start must be before 2000-01-01', $messages['start']);
        self::assertSame('ends must be after 2012-01-01', $messages['ends']);

        $v = $this->makeValidator(['x' => new DateTime('2000-01-01')], [Rules::make('x')->dateBefore('2012-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => new DateTime('2000-01-01'),
                'ends' => new Carbon('2013-01-01'),
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '2012-01-01',
                'ends' => new DateTime('2013-01-01'),
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => new DateTime('2012-01-01'),
                'ends' => new DateTime('2000-01-01'),
            ],
            [
                Rules::make('start')->dateAfter('2000-01-01'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 2012-01-01 00:00:00');

        $v = $this->makeValidator(
            [
                'start' => 'today',
                'ends' => 'tomorrow',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:43:59'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44')->dateAfter('2012-01-01 17:43:58')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:01'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:02')->dateAfter('2012-01-01 17:44')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 2012-01-01 17:44:00');

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44'],
            [Rules::make('x')->dateAfter('2012-01-01 17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 2012-01-01 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:43:59'],
            [Rules::make('x')->dateBefore('17:44')->dateAfter('17:43:58')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:01'],
            [Rules::make('x')->dateBefore('17:44:02')->dateAfter('17:44')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateBefore('17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateAfter('17:44:00')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 17:44:00');
    }

    /**
     * @test
     */
    public function date_before_and_after_with_format_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '31/12/2000'], [Rules::make('x')->dateBefore('31/02/2012')]);
        $this->assertValidationFail($v, 'x', 'x must be before 31/02/2012');

        $v = $this->makeValidator(['x' => ['31/12/2000']], [Rules::make('x')->dateBefore('31/02/2012')]);
        $this->assertValidationFail($v, 'x', 'x must be before 31/02/2012');

        $v = $this->makeValidator(['x' => '31/12/2000'], [Rules::make('x')->dateBefore('31/02/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '31/12/2012'], [Rules::make('x')->dateAfter('31/12/2000')]);
        $this->assertValidationFail($v, 'x', 'x must be after 31/12/2000');

        $v = $this->makeValidator(['x' => ['31/12/2012']], [Rules::make('x')->dateAfter('31/12/2000')]);
        $this->assertValidationFail($v, 'x', 'x must be after 31/12/2000');

        $v = $this->makeValidator(['x' => '31/12/2012'], [Rules::make('x')->dateAfter('31/12/2000', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateAfter('01/01/2000'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());

        $messages = $v->getMessages($this->makeValidationMessageResolver());
        self::assertSame('start must be after 01/01/2000', $messages['start']);
        self::assertSame('ends must be after start', $messages['ends']);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateAfter('31/12/2000', 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateAfter('31/12/2000'),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());

        $messages = $v->getMessages($this->makeValidationMessageResolver());
        self::assertSame('start must be after 31/12/2000', $messages['start']);
        self::assertSame('ends must be after start', $messages['ends']);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateAfter('31/12/2000', 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 31/12/2012');

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());

        $messages = $v->getMessages($this->makeValidationMessageResolver());
        self::assertSame('start must be before ends', $messages['start']);
        self::assertSame('ends must be after start', $messages['ends']);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends'), 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());

        $messages = $v->getMessages($this->makeValidationMessageResolver());
        self::assertSame('start must be before ends', $messages['start']);
        self::assertSame('ends must be after start', $messages['ends']);

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateBefore(Rules::reference('ends'), 'd/m/Y'),
                Rules::make('ends')->dateAfter(Rules::reference('start'), 'd/m/Y'),
            ]
        );
        self::assertFalse($v->passes());

        $messages = $v->getMessages($this->makeValidationMessageResolver());
        self::assertSame('start must be before 31/12/2000', $messages['start']);
        self::assertSame('ends must be after 31/12/2012', $messages['ends']);

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [Rules::make('x')->dateAfter('yesterday', 'd/m/Y')->dateBefore('tomorrow', 'd/m/Y')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [Rules::make('x')->dateAfter('today', 'd/m/Y')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after today');

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [Rules::make('x')->dateBefore('today', 'd/m/Y')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before today');

        $v = $this->makeValidator(
            ['x' => date('Y-m-d')],
            [Rules::make('x')->dateAfter('yesterday')->dateBefore('tomorrow')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => date('Y-m-d')],
            [Rules::make('x')->dateAfter('today')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after today');

        $v = $this->makeValidator(
            ['x' => date('Y-m-d')],
            [Rules::make('x')->dateBefore('today')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before today');

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:01', 'Y-m-d H:i:s')->dateAfter('2012-01-01 17:43:59', 'Y-m-d H:i:s')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [Rules::make('x')->dateBefore('2012-01-01 17:44:00', 'Y-m-d H:i:s')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 2012-01-01 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [Rules::make('x')->dateBefore('17:44:01', 'H:i:s')->dateAfter('17:43:59')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [Rules::make('x')->dateBefore('17:44:00', 'H:i:s')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [Rules::make('x')->dateAfter('17:44:00', 'H:i:s')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 17:44:00');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateBefore('17:45', 'H:i')->dateAfter('17:43')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateBefore('17:44', 'H:i')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before 17:44');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [Rules::make('x')->dateAfter('17:44', 'H:i')]
        );
        $this->assertValidationFail($v, 'x', 'x must be after 17:44');
    }

    /**
     * @test
     */
    public function date_before_and_after_with_format_from_date_format_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '31/12/2000'], [Rules::make('x')->dateFormat('d/m/Y')->dateBefore('31/02/2012')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '31/12/2012'], [Rules::make('x')->dateFormat('d/m/Y')->dateAfter('31/12/2000')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateAfter('31/12/2000'),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateAfter('31/12/2000'),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        $this->assertValidationFail($v, 'ends', 'ends must be after 31/12/2012');

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2013',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            [
                'start' => '31/12/2012',
                'ends' => '31/12/2000',
            ],
            [
                Rules::make('start')->dateFormat('d/m/Y')->dateBefore(Rules::reference('ends')),
                Rules::make('ends')->dateFormat('d/m/Y')->dateAfter(Rules::reference('start')),
            ]
        );
        self::assertFalse($v->passes());

        $messages = $v->getMessages($this->makeValidationMessageResolver());
        self::assertSame('start must be before 31/12/2000', $messages['start']);
        self::assertSame('ends must be after 31/12/2012', $messages['ends']);

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [Rules::make('x')->dateFormat('d/m/Y')->dateAfter('yesterday')->dateBefore('tomorrow')]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => date('d/m/Y')],
            [Rules::make('x')->dateFormat('d/m/Y')->dateBefore('today')]
        );
        $this->assertValidationFail($v, 'x', 'x must be before today');
    }

    /**
     * @test
     */
    public function date_before_and_after_or_equal_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateBeforeOrEqual('2012-01-15')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateBeforeOrEqual('2012-01-16')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateBeforeOrEqual('2012-01-14')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 2012-01-14');

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateBeforeOrEqual('15/01/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateBeforeOrEqual('14/01/2012', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 14/01/2012');

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateBeforeOrEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateBeforeOrEqual('tomorrow', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateBeforeOrEqual('yesterday', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal yesterday');

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateAfterOrEqual('2012-01-15')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateAfterOrEqual('2012-01-14')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-15'], [Rules::make('x')->dateAfterOrEqual('2012-01-16')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 2012-01-16');

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateAfterOrEqual('15/01/2012', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '15/01/2012'], [Rules::make('x')->dateAfterOrEqual('16/01/2012', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 16/01/2012');

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateAfterOrEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateAfterOrEqual('yesterday', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateAfterOrEqual('tomorrow', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal tomorrow');

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [
                Rules::make('x')
                    ->dateBeforeOrEqual('2012-01-01 17:44:00', 'Y-m-d H:i:s')
                    ->dateAfterOrEqual('2012-01-01 17:44:00', 'Y-m-d H:i:s'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '2012-01-01 17:44:00'],
            [
                Rules::make('x')
                    ->dateFormat('Y-m-d H:i:s')
                    ->dateBeforeOrEqual('2012-01-01 17:44:00')
                    ->dateAfterOrEqual('2012-01-01 17:44:00'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateBeforeOrEqual('2012-01-01 17:43:59', 'Y-m-d H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 2012-01-01 17:43:59');

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateAfterOrEqual('2012-01-01 17:44:01', 'Y-m-d H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 2012-01-01 17:44:01');

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [
                Rules::make('x')
                    ->dateBeforeOrEqual('17:44:00', 'H:i:s')
                    ->dateAfterOrEqual('17:44:00', 'H:i:s'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(
            ['x' => '17:44:00'],
            [
                Rules::make('x')
                    ->dateFormat('H:i:s')
                    ->dateBeforeOrEqual('17:44:00')
                    ->dateAfterOrEqual('17:44:00'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateBeforeOrEqual('17:43:59', 'H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 17:43:59');

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateAfterOrEqual('17:44:01', 'H:i:s')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 17:44:01');

        $v = $this->makeValidator(
            ['x' => '17:44'],
            [
                Rules::make('x')
                    ->dateBeforeOrEqual('17:44', 'H:i')
                    ->dateAfterOrEqual('17:44', 'H:i'),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateBeforeOrEqual('17:43', 'H:i')]);
        $this->assertValidationFail($v, 'x', 'x must be before or equal 17:43');

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateAfterOrEqual('17:45', 'H:i')]);
        $this->assertValidationFail($v, 'x', 'x must be after or equal 17:45');
    }

    /**
     * @test
     */
    public function date_equal_rule(): void
    {
        date_default_timezone_set('UTC');
        $v = $this->makeValidator(['x' => new Carbon('2000-01-01')], [Rules::make('x')->dateEqual('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateEqual('2000-01-01')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2000-01-01'], [Rules::make('x')->dateEqual('2001-01-01')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 2001-01-01');

        $v = $this->makeValidator(
            [
                'starts' => new DateTime('2000-01-01'),
                'ends' => new DateTime('2000-01-01'),
            ],
            [
                Rules::make('ends')->dateEqual(Rules::reference('starts')),
            ]
        );
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('Y-m-d')], [Rules::make('x')->dateEqual('today')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('Y-m-d')], [Rules::make('x')->dateEqual('yesterday')]);
        $this->assertValidationFail($v, 'x', 'x must be equal yesterday');

        $v = $this->makeValidator(['x' => date('Y-m-d')], [Rules::make('x')->dateEqual('tomorrow')]);
        $this->assertValidationFail($v, 'x', 'x must be equal tomorrow');

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateEqual('today', 'd/m/Y')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateEqual('yesterday', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be equal yesterday');

        $v = $this->makeValidator(['x' => date('d/m/Y')], [Rules::make('x')->dateEqual('tomorrow', 'd/m/Y')]);
        $this->assertValidationFail($v, 'x', 'x must be equal tomorrow');

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:44:00')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:43:59')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 2012-01-01 17:43:59');

        $v = $this->makeValidator(['x' => '2012-01-01 17:44:00'], [Rules::make('x')->dateFormat('Y-m-d H:i:s')->dateEqual('2012-01-01 17:44:01')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 2012-01-01 17:44:01');

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateFormat('H:i:s')->dateEqual('17:44:00')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateFormat('H:i:s')->dateEqual('17:43:59')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:43:59');

        $v = $this->makeValidator(['x' => '17:44:00'], [Rules::make('x')->dateFormat('H:i:s')->dateEqual('17:44:01')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:44:01');

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateFormat('H:i')->dateEqual('17:44')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateFormat('H:i')->dateEqual('17:43')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:43');

        $v = $this->makeValidator(['x' => '17:44'], [Rules::make('x')->dateFormat('H:i')->dateEqual('17:45')]);
        $this->assertValidationFail($v, 'x', 'x must be equal 17:45');
    }

    /**
     * @test
     */
    public function date_format_rule(): void
    {
        $v = $this->makeValidator(['foo' => '2000-01-01'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '01/01/2001'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => '22000-01-01'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => '00-01-01'], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => ['Not', 'a', 'date']], [Rules::make('foo')->dateFormat('Y-m-d')]);
        $this->assertValidationFail($v, 'foo', 'foo must match Y-m-d');

        $v = $this->makeValidator(['foo' => '2013-02'], [Rules::make('foo')->dateFormat('Y-m')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00Atlantic/Azores'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:se')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00Z'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:sT')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00+0000'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:sO')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01T00:00:00+00:30'], [Rules::make('foo')->dateFormat('Y-m-d\TH:i:sP')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01 17:43:59'], [Rules::make('foo')->dateFormat('Y-m-d H:i:s')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '2000-01-01 17:43:59'], [Rules::make('foo')->dateFormat('H:i:s')]);
        $this->assertValidationFail($v, 'foo', 'foo must match H:i:s');

        $v = $this->makeValidator(['foo' => '17:43:59'], [Rules::make('foo')->dateFormat('H:i:s')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '17:43:59'], [Rules::make('foo')->dateFormat('H:i')]);
        $this->assertValidationFail($v, 'foo', 'foo must match H:i');

        $v = $this->makeValidator(['foo' => '17:43'], [Rules::make('foo')->dateFormat('H:i')]);
        self::assertTrue($v->passes());
    }
}
