<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\FailedSegments;
use PHPUnit\Framework\TestCase;

final class FailedSegmentsTest extends TestCase
{
    /**
     * @test
     */
    public function it_checks_partial_path_failures(): void
    {
        $tracker = new FailedSegments();

        self::assertFalse($tracker->hasFailed('test'));

        $tracker->fail('test');

        self::assertTrue($tracker->hasFailed('test'));
        self::assertTrue($tracker->hasFailed('test.value'));
        self::assertFalse($tracker->hasFailed('key.0.value'));

        $tracker->fail('key.0.value');

        self::assertTrue($tracker->hasFailed('key.0.value'));
        self::assertTrue($tracker->hasFailed('key.0.value.demo'));
        self::assertFalse($tracker->hasFailed('key.0'));
        self::assertFalse($tracker->hasFailed('key'));
    }

    /**
     * @test
     */
    public function it_checks_partial_path_failures_with_wildcard(): void
    {
        $tracker = new FailedSegments();

        self::assertFalse($tracker->hasFailed('test'));

        $tracker->fail('test');

        self::assertTrue($tracker->hasFailed('test'));
        self::assertTrue($tracker->hasFailed('test.*'));
        self::assertTrue($tracker->hasFailed('test.1'));
    }
}
