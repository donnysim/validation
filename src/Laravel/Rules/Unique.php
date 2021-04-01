<?php

declare(strict_types=1);

namespace DonnySim\Validation\Laravel\Rules;

use DonnySim\Validation\Contracts\BatchRule;
use DonnySim\Validation\FieldReference;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use UnexpectedValueException;
use function array_column;
use function array_unique;
use function gettype;
use function is_int;
use function is_string;

class Unique implements BatchRule
{
    public const NAME = 'laravel.unique';

    /**
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    protected string $column;

    /**
     * @var \DonnySim\Validation\FieldReference|int|int[]|string|string[]|null
     */
    protected $exceptValue = null;

    protected ?string $exceptColumn = null;

    public function __construct($target, string $column)
    {
        $this->builder = $this->getBuilder($target);
        $this->column = $this->getColumn($column, $target);
    }

    public static function make($target, string $column): self
    {
        return new static($target, $column);
    }

    /**
     * @param \DonnySim\Validation\FieldReference|string|int|string[]|int[] $value
     * @param string $column
     *
     * @return static
     */
    public function except($value, string $column = 'id'): self
    {
        $this->exceptValue = $value;
        $this->exceptColumn = $column;

        return $this;
    }

    /**
     * @param \DonnySim\Validation\Data\EntryPipeline[] $pipelines
     */
    public function handle(array $pipelines): void
    {
        $entries = [];
        $hasReferenceExceptions = false;

        foreach ($pipelines as $pipeline) {
            if ($pipeline->getEntry()->isMissing()) {
                continue;
            }

            $value = (array)$pipeline->getEntry()->getValue();
            $entry = [
                'value' => [],
                'except' => null,
            ];

            foreach ($value as $item) {
                if ($item !== null) {
                    $entry['value'][] = $item;
                }
            }

            if ($this->exceptValue && $this->exceptValue instanceof FieldReference) {
                $reference = $pipeline->getValidator()->getValueEntry($pipeline->getEntry()->resolvePathWildcards($this->exceptValue->getField()));
                $referenceValue = $reference->getValue();

                if (!$reference->isMissing() && (is_string($referenceValue) || is_int($referenceValue))) {
                    $entry['except'] = $referenceValue;
                }
            }

            if (!empty($entry['value'])) {
                $entries[] = $entry;

                if ($entry['except'] !== null) {
                    $hasReferenceExceptions = true;
                }
            }
        }

        if (empty($entries)) {
            return;
        }

        if ($hasReferenceExceptions) {
            $builder = $this->builder
                ->where(function ($builder) use ($entries) {
                    foreach ($entries as $entry) {
                        $builder
                            ->orWhere(function ($builder) use ($entry) {
                                $builder
                                    ->whereIn($this->column, $entry['value'])
                                    ->where($this->exceptColumn, '<>', $entry['except']);
                            });
                    }
                });
        } else {
            $builder = $this->builder
                ->when($this->exceptValue !== null, function ($builder) {
                    $builder->whereNotIn($this->exceptColumn, (array)$this->exceptValue);
                })
                ->whereIn($this->column, array_unique(Arr::flatten(array_column($entries, 'value'))));
        }

        $occurrences = $builder->pluck($this->column)->map(fn($key) => (string)$key);

        foreach ($pipelines as $pipeline) {
            if ($pipeline->getEntry()->isMissing()) {
                continue;
            }

            $value = (array)$pipeline->getEntry()->getValue();

            foreach ($value as $item) {
                if ($item !== null && $occurrences->contains((string)$item)) {
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

        if (is_string($value)) {
            return Manager::table($value);
        }

        throw new UnexpectedValueException('Cannot infer builder from parameter of type ' . gettype($value));
    }

    protected function getColumn($column, $target): string
    {
        if (is_string($column)) {
            return $column;
        }

        if ($target instanceof Model) {
            return $target->getQualifiedKeyName();
        }

        throw new InvalidArgumentException('Cannot infer column from parameter type ' . gettype($target));
    }
}
