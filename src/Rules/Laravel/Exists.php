<?php

declare(strict_types=1);

namespace DonnySim\Validation\Rules\Laravel;

use DonnySim\Validation\Contracts\BatchRule;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;
use UnexpectedValueException;

class Exists implements BatchRule
{
    public const NAME = 'laravel.exists';

    /**
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    protected string $column;

    public function __construct($target, ?string $column = null)
    {
        $this->builder = $this->getBuilder($target);
        $this->column = $this->getColumn($column, $target);
    }

    public static function make($target, ?string $column = null): self
    {
        return new static($target, $column);
    }

    /**
     * @param \DonnySim\Validation\EntryPipeline[] $pipelines
     */
    public function handle(array $pipelines): void
    {
        $keys = [];

        foreach ($pipelines as $pipeline) {
            if ($pipeline->getEntry()->isMissing()) {
                continue;
            }

            $value = (array)$pipeline->getEntry()->getValue();

            foreach ($value as $item) {
                if ($item !== null) {
                    $keys[] = $item;
                }
            }
        }

        $keys = \array_unique($keys);

        if (empty($keys)) {
            return;
        }

        $occurrences = $this->builder
            ->whereIn($this->column, $keys)
            ->pluck($this->column)
            ->map(fn($key) => (string)$key);

        foreach ($pipelines as $pipeline) {
            if ($pipeline->getEntry()->isMissing()) {
                continue;
            }

            $value = (array)$pipeline->getEntry()->getValue();

            foreach ($value as $item) {
                if ($item !== null && !$occurrences->contains((string)$item)) {
                    $pipeline->fail(static::NAME);
                    break;
                }
            }
        }
    }

    /**
     * @param $value
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function getBuilder($value)
    {
        if ($value instanceof QueryBuilder || $value instanceof EloquentBuilder) {
            return $value;
        }

        if (\is_string($value)) {
            return Manager::table($value);
        }

        throw new UnexpectedValueException('Cannot infer builder from parameter of type ' . \gettype($value));
    }

    protected function getColumn($column, $target): string
    {
        if (\is_string($column)) {
            return $column;
        }

        if ($target instanceof Model) {
            return $target->getQualifiedKeyName();
        }

        throw new InvalidArgumentException('Cannot infer column from parameter type ' . \gettype($target));
    }
}
