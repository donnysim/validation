<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Tests\Traits\ValidationHelpersTrait;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TypeRulesTest extends TestCase
{
    use ValidationHelpersTrait;

    /**
     * @test
     */
    public function array_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [RuleSet::make('foo')->arrayType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be array']]);

        $v = $this->makeValidator([], [RuleSet::make('foo')->arrayType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [RuleSet::make('foo')->arrayType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar' => 'baz']], [RuleSet::make('foo')->arrayType()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function boolean_type_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator(['foo' => 'yes'], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator(['foo' => 'false'], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator(['foo' => 'true'], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator([], [RuleSet::make('foo')->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => false], [RuleSet::make('foo')->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => true], [RuleSet::make('foo')->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator(['foo' => '0'], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);
    }

    /**
     * @test
     */
    public function boolean_like_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'yes'], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'false'], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator([], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => false], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => true], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '0'], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->booleanLike()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'asd'], [RuleSet::make('foo')->booleanLike()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean like']]);

        $v = $this->makeValidator(['foo' => [true]], [RuleSet::make('foo')->booleanLike()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean like']]);

        $v = $this->makeValidator(['foo' => 2], [RuleSet::make('foo')->booleanLike()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean like']]);
    }

    /**
     * @test
     */
    public function numeric_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->numeric()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric']]);

        $v = $this->makeValidator(['foo' => new stdClass()], [RuleSet::make('foo')->numeric()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric']]);

        $v = $this->makeValidator(['foo' => 'asdad'], [RuleSet::make('foo')->numeric()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric']]);

        $v = $this->makeValidator(['foo' => '1.23'], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1.23'], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1'], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1.1], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1.1], [RuleSet::make('foo')->numeric()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function numeric_float_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric float']]);

        $v = $this->makeValidator(['foo' => new stdClass()], [RuleSet::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric float']]);

        $v = $this->makeValidator(['foo' => 'asdad'], [RuleSet::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric float']]);

        $v = $this->makeValidator(['foo' => '1.23'], [RuleSet::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1.23'], [RuleSet::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '-1'], [RuleSet::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric float']]);

        $v = $this->makeValidator(['foo' => '1'], [RuleSet::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric float']]);

        $v = $this->makeValidator(['foo' => -1], [RuleSet::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric float']]);

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->numericFloat()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric float']]);

        $v = $this->makeValidator(['foo' => -1.1], [RuleSet::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1.1], [RuleSet::make('foo')->numericFloat()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function numeric_integer_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric integer']]);

        $v = $this->makeValidator(['foo' => new stdClass()], [RuleSet::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric integer']]);

        $v = $this->makeValidator(['foo' => 'asdad'], [RuleSet::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric integer']]);

        $v = $this->makeValidator(['foo' => '1.23'], [RuleSet::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric integer']]);

        $v = $this->makeValidator(['foo' => '-1.23'], [RuleSet::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric integer']]);

        $v = $this->makeValidator(['foo' => '-1'], [RuleSet::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [RuleSet::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1], [RuleSet::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->numericInteger()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => -1.1], [RuleSet::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric integer']]);

        $v = $this->makeValidator(['foo' => 1.1], [RuleSet::make('foo')->numericInteger()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be numeric integer']]);
    }

    /**
     * @test
     */
    public function integer_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [RuleSet::make('foo')->integerType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be integer']]);

        $v = $this->makeValidator([], [RuleSet::make('foo')->integerType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->integerType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1.12], [RuleSet::make('foo')->integerType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be integer']]);

        $v = $this->makeValidator(['foo' => '1'], [RuleSet::make('foo')->integerType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be integer']]);
    }

    /**
     * @test
     */
    public function string_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->stringType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->stringType()]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['foo' => 'asd'], [RuleSet::make('foo')->stringType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->stringType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be string']]);
    }
}
