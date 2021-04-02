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
        self::assertSame([[false, 'path', null, []]], $this->walk([], 'path'));
        self::assertSame([[true, 'path', 'value', []]], $this->walk(['path' => 'value'], 'path'));
    }

    /**
     * @test
     */
    public function nested_object_keys(): void
    {
        self::assertSame([[false, 'first.second', null, []]], $this->walk([], 'first.second'));
        self::assertSame([[false, 'first.second', null, []]], $this->walk(['first' => 'value'], 'first.second'));
        self::assertSame([[true, 'first.second', 'value', []]], $this->walk([
            'first' => [
                'second' => 'value',
            ],
        ], 'first.second'));
    }

    /**
     * @test
     */
    public function wildcard_root(): void
    {
        self::assertSame([], $this->walk([], '*'));
        self::assertSame([[true, '0', 'first', ['0']], [true, '1', 'second', ['1']]], $this->walk(['first', 'second'], '*'));
    }

    /**
     * @test
     */
    public function wildcard_nested(): void
    {
        self::assertSame([], $this->walk([], '*.*'));
        self::assertSame([[false, '0.name', null, ['0']], [false, '1.name', null, ['1']]], $this->walk(['first', 'second'], '*.name'));
        self::assertSame([[true, '0.name', 'first', ['0']], [true, 'second.name', 'second', ['second']]], $this->walk([
            [
                'name' => 'first',
            ],
            'second' => [
                'name' => 'second',
            ],
        ], '*.name'));
        self::assertSame([[true, 'first.name', 'first', ['first']], [true, 'second.name', 'second', ['second']]], $this->walk([
            'first' => [
                'name' => 'first',
            ],
            'second' => [
                'name' => 'second',
            ],
        ], '*.name'));
        self::assertSame([[true, 'first.0', '1', ['first', '0']], [true, 'first.1', '2', ['first', '1']], [true, 'second.name', 'second', ['second', 'name']]], $this->walk([
            'first' => ['1', '2'],
            'second' => ['name' => 'second'],
        ], '*.*'));
        self::assertSame([[true, 'first.0.0', '1', ['first', '0', '0']], [true, 'first.0.1', '2', ['first', '0', '1']]], $this->walk([
            'first' => [
                [
                    '1',
                    '2'
                ],
            ],
        ], '*.*.*'));
    }

    protected function walk(array $data, string $path): ?array
    {
        $result = [];
        $walker = new PathWalker($data);

        foreach ($walker->walk($path) as $entry) {
            $result[] = [$entry->exists(), $entry->getPath(), $entry->getValue(), $entry->getWildcards()];
        }

        return $result;
    }
}
