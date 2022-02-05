<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\RuleSet;
use DonnySim\Validation\Tests\Traits\ValidationHelpersTrait;
use PHPUnit\Framework\TestCase;

final class CastRulesTest extends TestCase
{
    use ValidationHelpersTrait;

    /**
     * @test
     */
    public function cast_to_boolean(): void
    {
        $v = $this->makeValidator(['foo' => true], [RuleSet::make('foo')->toBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'true'], [RuleSet::make('foo')->toBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->toBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'on'], [RuleSet::make('foo')->toBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'yes'], [RuleSet::make('foo')->toBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->toBoolean()]);
        $data = $v->getValidatedData();
        self::assertFalse($data['foo']);

        $v = $this->makeValidator(['foo' => 'anything non booleanish'], [RuleSet::make('foo')->toBoolean()]);
        $data = $v->getValidatedData();
        self::assertFalse($data['foo']);
    }

    /**
     * @test
     */
    public function cast_to_integer(): void
    {
        $v = $this->makeValidator(['foo' => true], [RuleSet::make('foo')->toInteger()]);
        $data = $v->getValidatedData();
        self::assertSame(1, $data['foo']);

        $v = $this->makeValidator(['foo' => 'asd'], [RuleSet::make('foo')->toInteger()]);
        $data = $v->getValidatedData();
        self::assertSame(0, $data['foo']);

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->toInteger()]);
        $data = $v->getValidatedData();
        self::assertSame(1, $data['foo']);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->toInteger()]);
        $data = $v->getValidatedData();
        self::assertSame(0, $data['foo']);

        $v = $this->makeValidator(['foo' => false], [RuleSet::make('foo')->toInteger()]);
        $data = $v->getValidatedData();
        self::assertSame(0, $data['foo']);
    }

    /**
     * @test
     */
    public function cast_to_string(): void
    {
        $v = $this->makeValidator(['foo' => true], [RuleSet::make('foo')->toString()]);
        $data = $v->getValidatedData();
        self::assertSame('1', $data['foo']);

        $v = $this->makeValidator(['foo' => 'true'], [RuleSet::make('foo')->toString()]);
        $data = $v->getValidatedData();
        self::assertSame('true', $data['foo']);

        $v = $this->makeValidator(['foo' => 1], [RuleSet::make('foo')->toString()]);
        $data = $v->getValidatedData();
        self::assertSame('1', $data['foo']);

        $v = $this->makeValidator(['foo' => null], [RuleSet::make('foo')->toString()]);
        $data = $v->getValidatedData();
        self::assertSame('', $data['foo']);
    }
}
