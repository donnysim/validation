<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\Rules;
use DonnySim\Validation\Tests\Concerns\ValidatorHelpers;
use PHPUnit\Framework\TestCase;

final class CastRulesTest extends TestCase
{
    use ValidatorHelpers;

    /**
     * @test
     */
    public function cast_to_boolean(): void
    {
        $v = $this->makeValidator(['foo' => true], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'true'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 1], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'on'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => 'yes'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertTrue($data['foo']);

        $v = $this->makeValidator(['foo' => null], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertFalse($data['foo']);

        $v = $this->makeValidator(['foo' => 'anything non booleanish'], [Rules::make('foo')->castToBoolean()]);
        $data = $v->getValidatedData();
        self::assertFalse($data['foo']);
    }
}
