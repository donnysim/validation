<?php

declare(strict_types=1);

namespace DonnySim\Validation\Tests;

use DonnySim\Validation\PathWalker;
use PHPUnit\Framework\TestCase;

class PathWalkerTest extends TestCase
{
    /**
     * @test
     */
    public function object_keys(): void
    {
        self::assertSame($this->walk([], 'path'), [[false, 'path', []]]);
        self::assertSame($this->walk(['path' => 'value'], 'path'), [[true, 'path', 'value', []]]);
    }

    /**
     * @test
     */
    public function nested_object_keys(): void
    {
        self::assertSame($this->walk([], 'first.second'), [[false, 'first.second', []]]);
        self::assertSame($this->walk(['first' => 'value'], 'first.second'), [[false, 'first.second', []]]);
        self::assertSame($this->walk([
            'first' => [
                'second' => 'value',
            ],
        ], 'first.second'), [[true, 'first.second', 'value', []]]);
    }

    /**
     * @test
     */
    public function wildcard_root(): void
    {
        self::assertSame($this->walk([], '*'), []);
        self::assertSame($this->walk(['first', 'second'], '*'), [[true, '0', 'first', ['0']], [true, '1', 'second', ['1']]]);
    }

    /**
     * @test
     */
    public function wildcard_nested(): void
    {
        self::assertSame($this->walk([], '*.*'), []);
        self::assertSame($this->walk(['first', 'second'], '*.name'), [[false, '0.name', ['0']], [false, '1.name', ['1']]]);
        self::assertSame($this->walk([
            [
                'name' => 'first',
            ],
            'second' => [
                'name' => 'second',
            ],
        ], '*.name'), [[true, '0.name', 'first', ['0']], [true, 'second.name', 'second', ['second']]]);
        self::assertSame($this->walk([
            'first' => [
                'name' => 'first',
            ],
            'second' => [
                'name' => 'second',
            ],
        ], '*.name'), [[true, 'first.name', 'first', ['first']], [true, 'second.name', 'second', ['second']]]);
        self::assertSame($this->walk([
            'first' => ['1', '2'],
            'second' => ['name' => 'second'],
        ], '*.*'), [[true, 'first.0', '1', ['first', '0']], [true, 'first.1', '2', ['first', '1']], [true, 'second.name', 'second', ['second', 'name']]]);
        self::assertSame($this->walk([
            'first' => [
                [
                    '1',
                    '2'
                ],
            ],
        ], '*.*.*'), [[true, 'first.0.0', '1', ['first', '0', '0']], [true, 'first.0.1', '2', ['first', '0', '1']]]);
    }

    protected function walk(array $data, string $path): ?array
    {
        $result = [];
        $walker = new PathWalker($data);
        $walker
            ->onHit(static function (string $path, $value, array $wildcards) use (&$result) {
                $result[] = [true, $path, $value, $wildcards];
            })
            ->onMiss(static function (string $path, array $wildcards) use (&$result) {
                $result[] = [false, $path, $wildcards];
            });
        $walker->walk($path);

        return $result;
    }
}
