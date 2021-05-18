<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\Rules;
use DonnySim\Validation\Rules\Nullable;
use DonnySim\Validation\Rules\Required;
use DonnySim\Validation\Tests\Concerns\ValidatorHelpers;
use PHPUnit\Framework\TestCase;

final class OtherRulesTest extends TestCase
{
    use ValidatorHelpers;

    /**
     * @test
     */
    public function accepted_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'no'], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator([], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => false], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => 'false'], [Rules::make('foo')->accepted()]);
        $this->assertValidationFail($v, 'foo', 'foo must be accepted');

        $v = $this->makeValidator(['foo' => 'yes'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'on'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => true], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->accepted()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function confirmed(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->confirmed()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->confirmed()]);
        $this->assertValidationFail($v, 'foo', 'foo must be confirmed');

        $v = $this->makeValidator(['foo' => 'bar', 'foo_confirmation' => 'baz'], [Rules::make('foo')->confirmed()]);
        $this->assertValidationFail($v, 'foo', 'foo must be confirmed');

        $v = $this->makeValidator(['foo' => 'bar', 'foo_confirmation' => 'bar'], [Rules::make('foo')->confirmed()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => '1e2', 'foo_confirmation' => '100'], [Rules::make('foo')->confirmed()]);
        $this->assertValidationFail($v, 'foo', 'foo must be confirmed');
    }

    /**
     * @test
     */
    public function filled_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ''], [Rules::make('foo')->filled()]);
        $this->assertValidationFail($v, 'foo', 'foo must be filled');

        $v = $this->makeValidator(['foo' => [['id' => 1], []]], [Rules::make('foo.*.id')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => '']]], [Rules::make('foo.*.id')->filled()]);
        $this->assertValidationFail($v, 'foo.0.id', 'foo.0.id must be filled');

        $v = $this->makeValidator(['foo' => [['id' => null]]], [Rules::make('foo.*.id')->filled()]);
        $this->assertValidationFail($v, 'foo.0.id', 'foo.0.id must be filled');
    }

    /**
     * @test
     */
    public function nullable_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->nullable()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->nullable()->booleanType()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->nullable()->booleanType()]);
        $this->assertValidationFail($v, 'foo', 'foo must be boolean');

        $v = $this->makeValidator(['foo' => [null, 'true']], [Rules::make('foo.*')->nullable()->booleanType()]);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 must be boolean');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->nullable()->required()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function present_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->present()]);
        $this->assertValidationFail($v, 'foo', 'foo must be present');

        $v = $this->makeValidator([], [Rules::make('foo')->present()->nullable()]);
        $this->assertValidationFail($v, 'foo', 'foo must be present');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ''], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['name' => 'a']]], [Rules::make('foo.*.id')->present()]);
        $this->assertValidationFail($v, 'foo.1.id', 'foo.1.id must be present');

        $v = $this->makeValidator(['foo' => [['id' => 1], []]], [Rules::make('foo.*.id')->present()]);
        $this->assertValidationFail($v, 'foo.1.id', 'foo.1.id must be present');

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => '']]], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => null]]], [Rules::make('foo')->present()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function in_rule(): void
    {
        $v = $this->makeValidator(['foo' => 'foo'], [Rules::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must be in array');

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must be in array');

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->in(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar', 'baz']], [Rules::make('foo')->in(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must be in array');
    }

    /**
     * @test
     */
    public function not_in_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'foo'], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 0], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'bar'], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        $this->assertValidationFail($v, 'foo', 'foo must not be in array');

        $v = $this->makeValidator(['foo' => ['bar', 'baz']], [Rules::make('foo')->notIn(['bar', 'baz'])]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function required_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => ''], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => []], [Rules::make('foo')->required()]);
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(['foo' => 'name'], [Rules::make('foo')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['name']], [Rules::make('foo')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [Rules::make('foo.*')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [null]], [Rules::make('foo.*')->required()]);
        $this->assertValidationFail($v, 'foo.0', 'foo.0 is required');

        $v = $this->makeValidator(['foo' => ['bar']], [Rules::make('foo.*')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar', null]], [Rules::make('foo.*')->required()]);
        $this->assertValidationFail($v, 'foo.1', 'foo.1 is required');
    }

    /**
     * @test
     */
    public function set_value_if_missing_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->setValueIfMissing('missing')]);
        self::assertTrue($v->passes());
        $data = $v->getValidatedData();
        self::assertSame('missing', $data['foo']);

        $v = $this->makeValidator(['foo' => 'shoo'], [Rules::make('foo')->setValueIfMissing('missing')]);
        self::assertTrue($v->passes());
        $data = $v->getValidatedData();
        self::assertSame('shoo', $data['foo']);

        $v = $this->makeValidator(['foo' => [['bar' => 'baz'], []]], [Rules::make('foo.*.bar')->setValueIfMissing('baz')]);
        self::assertTrue($v->passes());
        $data = $v->getValidatedData();
        self::assertSame('baz', $data['foo'][0]['bar']);
        self::assertSame('baz', $data['foo'][1]['bar']);
    }

    /**
     * @test
     */
    public function sometimes_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->sometimes()->required()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function rule_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->rule(new Required())]);
        self::assertFalse($v->passes());
    }

    /**
     * @test
     */
    public function rules_rule(): void
    {
        $v = $this->makeValidator([], [Rules::make('foo')->rules([new Nullable(), new Required()])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->rules([new Nullable(), new Required()])]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function when_rule(): void
    {
        $v = $this->makeValidator(
            [],
            [
                Rules::make('foo')->when(true, static function (Rules $rules) {
                    $rules->required();
                }),
            ]
        );
        $this->assertValidationFail($v, 'foo', 'foo is required');

        $v = $this->makeValidator(
            [],
            [
                Rules::make('foo')->when(false, static function (Rules $rules) {
                    $rules->required();
                }),
            ]
        );
        self::assertTrue($v->passes());
    }
}
