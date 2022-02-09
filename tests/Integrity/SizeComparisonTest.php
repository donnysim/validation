<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests\Integrity;

use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Tests\Traits\ValidationHelpersTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use function ini_set;

final class SizeComparisonTest extends TestCase
{
    use ValidationHelpersTrait;

    /**
     * @test
     */
    public function less_than_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => new stdClass()], [RuleSet::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => 'aslksd'], [RuleSet::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => 'anc'], [RuleSet::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => '211'], [RuleSet::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => '22'], [RuleSet::make('foo')->lessThan(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [RuleSet::make('foo')->lessThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should contain less than 3 items']]);

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->lessThan(3.1)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3.1']]);

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->lessThan('3.1')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3.1']]);

        $v = $this->makeValidator(['foo' => 2.9], [RuleSet::make('foo')->lessThan('3.0')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->lessThan(2)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 2']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->lessThan(2.1)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 2.1']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->lessThan('2.1')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 2.1']]);

        $v = $this->makeValidator(['foo' => '4.1'], [RuleSet::make('foo')->numeric()->lessThan('4.1')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 4.1']]);
    }

    /**
     * @test
     */
    public function less_than_or_equal_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => new stdClass()], [RuleSet::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => 'aslksd'], [RuleSet::make('foo')->lessThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 3 length']]);

        $v = $this->makeValidator(['foo' => 'anc'], [RuleSet::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '211'], [RuleSet::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '22'], [RuleSet::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [RuleSet::make('foo')->lessThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2, 3]], [RuleSet::make('foo')->lessThanOrEqual(2)]);
        $this->assertValidationFail($v, ['foo' => ['foo should contain less than 2 items']]);

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->lessThanOrEqual(3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->lessThanOrEqual('3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [RuleSet::make('foo')->lessThanOrEqual('3.0')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->lessThanOrEqual(2)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 2']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->lessThanOrEqual(2.1)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 2.1']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->lessThanOrEqual('2.1')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be less than 2.1']]);

        $v = $this->makeValidator(['foo' => '4.1'], [RuleSet::make('foo')->numeric()->lessThanOrEqual('4.1')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function greater_than_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => new stdClass()], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => 3], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3']]);

        $v = $this->makeValidator(['foo' => 2], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3']]);

        $v = $this->makeValidator(['foo' => 'abc'], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => 'ab'], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [RuleSet::make('foo')->greaterThan(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2]], [RuleSet::make('foo')->greaterThan(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should contain more than 3 items']]);

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->greaterThan(3.1)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3.1']]);

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->greaterThan('3.1')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3.1']]);

        $v = $this->makeValidator(['foo' => 2.9], [RuleSet::make('foo')->greaterThan('3.0')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->greaterThan(4)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 4']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->greaterThan(4.1)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 4.1']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->greaterThan('4.1')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 4.1']]);

        $v = $this->makeValidator(['foo' => '4.2'], [RuleSet::make('foo')->numeric()->greaterThan('4.1')]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function greater_than_or_equal_rule(): void
    {
        ini_set('precision', '17');

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => new stdClass()], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => 3], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3']]);

        $v = $this->makeValidator(['foo' => 'abc'], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'ab'], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3 length']]);

        $v = $this->makeValidator(['foo' => [1, 2, 3, 4]], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [1, 2]], [RuleSet::make('foo')->greaterThanOrEqual(3)]);
        $this->assertValidationFail($v, ['foo' => ['foo should contain more than 3 items']]);

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->greaterThanOrEqual(3.1)]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 3.1], [RuleSet::make('foo')->greaterThanOrEqual('3.1')]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 2.9], [RuleSet::make('foo')->greaterThanOrEqual('3.0')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 3']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->greaterThanOrEqual(4)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 4']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->greaterThanOrEqual(4.1)]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 4.1']]);

        $v = $this->makeValidator(['foo' => '3'], [RuleSet::make('foo')->numeric()->greaterThanOrEqual('4.1')]);
        $this->assertValidationFail($v, ['foo' => ['foo should be greater than 4.1']]);

        $v = $this->makeValidator(['foo' => '4.1'], [RuleSet::make('foo')->numeric()->greaterThanOrEqual('4.1')]);
        self::assertTrue($v->passes());
    }
}
