<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\Data\DataEntry;
use DonnySim\Validation\Process\EntryProcess;
use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Rules\Base\Nullable;
use DonnySim\Validation\Rules\Base\Required;
use DonnySim\Validation\Tests\Traits\ValidationHelpersTrait;
use PHPUnit\Framework\TestCase;

final class BaseRulesTest extends TestCase
{
    use ValidationHelpersTrait;

    /**
     * @test
     */
    public function nullable_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->nullable()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator([], [RuleSet::make('foo')->nullable()->required()]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->nullable()->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => 'true'], [RuleSet::make('foo')->nullable()->booleanType()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be boolean']]);

        $v = $this->makeValidator(['foo' => [null, 'true']], [RuleSet::make('foo.*')->nullable()->booleanType()]);
        $this->assertValidationFail($v, ['foo.1' => ['foo.1 must be boolean']]);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->nullable()->required()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function required_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->required()]);
        $this->assertValidationFail($v, ['foo' => ['foo is required']]);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->required()]);
        $this->assertValidationFail($v, ['foo' => ['foo is required']]);

        $v = $this->makeValidator(['foo' => ''], [RuleSet::make('foo')->required()]);
        $this->assertValidationFail($v, ['foo' => ['foo is required']]);

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [RuleSet::make('foo')->required()]);
        $this->assertValidationFail($v, ['foo' => ['foo is required']]);

        $v = $this->makeValidator(['foo' => 'name'], [RuleSet::make('foo')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['name']], [RuleSet::make('foo')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [RuleSet::make('foo.*')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [null]], [RuleSet::make('foo.*')->required()]);
        $this->assertValidationFail($v, ['foo.0' => ['foo.0 is required']]);

        $v = $this->makeValidator(['foo' => ['bar']], [RuleSet::make('foo.*')->required()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar', null]], [RuleSet::make('foo.*')->required()]);
        $this->assertValidationFail($v, ['foo.1' => ['foo.1 is required']]);
    }

    /**
     * @test
     */
    public function filled_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->filled()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be filled']]);

        $v = $this->makeValidator(['foo' => ''], [RuleSet::make('foo')->filled()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be filled']]);

        $v = $this->makeValidator(['foo' => 0], [RuleSet::make('foo')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [RuleSet::make('foo')->filled()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be filled']]);

        $v = $this->makeValidator(['foo' => 'name'], [RuleSet::make('foo')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['name']], [RuleSet::make('foo')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => []], [RuleSet::make('foo.*')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [null]], [RuleSet::make('foo.*')->filled()]);
        $this->assertValidationFail($v, ['foo.0' => ['foo.0 must be filled']]);

        $v = $this->makeValidator(['foo' => ['bar']], [RuleSet::make('foo.*')->filled()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ['bar', null]], [RuleSet::make('foo.*')->filled()]);
        $this->assertValidationFail($v, ['foo.1' => ['foo.1 must be filled']]);
    }

    /**
     * @test
     */
    public function present_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->present()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be present']]);

        $v = $this->makeValidator([], [RuleSet::make('foo')->present()->nullable()]);
        $this->assertValidationFail($v, ['foo' => ['foo must be present']]);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => ''], [RuleSet::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['name' => 'a']]], [RuleSet::make('foo.*.id')->present()]);
        $this->assertValidationFail($v, ['foo.1.id' => ['foo.1.id must be present']]);

        $v = $this->makeValidator(['foo' => [['id' => 1], []]], [RuleSet::make('foo.*.id')->present()]);
        $this->assertValidationFail($v, ['foo.1.id' => ['foo.1.id must be present']]);

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => '']]], [RuleSet::make('foo')->present()]);
        self::assertTrue($v->passes());

        $v = $this->makeValidator(['foo' => [['id' => 1], ['id' => null]]], [RuleSet::make('foo')->present()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function optional_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->optional()->required()]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function rule_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->rule(new Required())]);
        self::assertFalse($v->passes());
    }

    /**
     * @test
     */
    public function rules_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->rules([new Nullable(), new Required()])]);
        self::assertFalse($v->passes());

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->rules([new Nullable(), new Required()])]);
        self::assertTrue($v->passes());
    }

    /**
     * @test
     */
    public function set_value_if_missing_rule(): void
    {
        $v = $this->makeValidator([], [RuleSet::make('foo')->setValueIfNotPresent('missing')]);
        self::assertTrue($v->passes());
        $data = $v->getValidatedData();
        self::assertSame('missing', $data['foo']);

        $v = $this->makeValidator(['foo' => 'shoo'], [RuleSet::make('foo')->setValueIfNotPresent('missing')]);
        self::assertTrue($v->passes());
        $data = $v->getValidatedData();
        self::assertSame('shoo', $data['foo']);
    }


    /**
     * @test
     */
    public function pipe_rule(): void
    {
        $pipeCalled = false;

        $v = $this->makeValidator(['foo' => null], [
            RuleSet::make('foo')
                ->pipe(static function (DataEntry $entry, EntryProcess $process) use (&$pipeCalled) {
                    $pipeCalled = true;
                })
                ->required()
        ]);

        self::assertTrue($v->fails());
        self::assertTrue($pipeCalled);
    }
}
